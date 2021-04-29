<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\helper\Text;
use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\server\SwooleServer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends Command implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    public const COMMAND_NAME = 'start';

    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * ServerStartCommand constructor.
     *
     * @param ServerInterface  $server
     * @param ServerProperties $serverProperties
     */
    public function __construct(ServerInterface $server, ServerProperties $serverProperties)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->server = $server;
        $this->serverProperties = $serverProperties;
    }

    protected function configure(): void
    {
        $this->setDescription('start php server');
        $this->addOption('server', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->serverProperties->isExternalMode() || $input->getOption('server')) {
            if ($this->server instanceof SwooleServer) {
                Coroutine::enable();
            }
            $this->server->start();
        } else {
            $this->writePidFile();
            $this->startService($input);
        }

        return 0;
    }

    private function writePidFile(): void
    {
        file_put_contents($this->serverProperties->getServerPidFile(), getmypid());
    }

    public static function withFileLock(string $filePrefix, callable $callback): bool
    {
        $lockFile = $filePrefix.'.lock';
        $fp = fopen($lockFile, 'wb+');
        if (false !== $fp && flock($fp, LOCK_EX)) {  // 进行排它型锁定
            try {
                $callback();
            } finally {
                flock($fp, LOCK_UN);    // 释放锁定
                fclose($fp);
                @unlink($lockFile);
            }

            return true;
        }

        return false;
    }

    private function startService(InputInterface $input): void
    {
        $confPath = $this->serverProperties->getSupervisorConfPath();
        if (null === $confPath || !is_dir($confPath)) {
            throw new \RuntimeException('tars.application.server.supervisor_conf_path cannot be empty when start_mode is external');
        }
        $serviceName = $this->serverProperties->getServerName();
        $configFile = $confPath.'/'.$serviceName.$this->serverProperties->getSupervisorConfExtension();
        $ret = self::withFileLock($configFile, function () use ($serviceName, $configFile) {
            $env = $this->serverProperties->getEnv() ?? '';
            if (Text::isNotEmpty($this->serverProperties->getEmalloc())) {
                $env = (!empty($env) ? ',' : '')
                    .sprintf('USE_ZEND_ALLOC="0",LD_PRELOAD="%s"', $this->serverProperties->getEmalloc());
            }
            $configContent = strtr('[program:{server_name}]
directory={cwd}
environment={env}
command={php} {script_file} --config={conf_file} start --server
stdout_logfile={log_file}
redirect_stderr=true
', [
                '{cwd}' => getcwd(),
                '{server_name}' => $serviceName,
                '{php}' => PHP_BINARY,
                '{env}' => $env,
                '{script_file}' => realpath($_SERVER['SCRIPT_FILENAME']),
                '{log_file}' => $this->serverProperties->getAppLogPath().'/'.$serviceName.'.log',
                '{conf_file}' => realpath(ServerApplication::getInstance()->getConfigFile()),
            ]);
            $supervisorctl = $this->serverProperties->getSupervisorctl() ?? 'supervisorctl';
            if (!file_exists($configFile) || (file_get_contents($configFile) !== $configContent)) {
                $this->logger->info(static::TAG."create supervisor config $configFile");
                file_put_contents($configFile, $configContent);
                system("$supervisorctl reread", $ret);
                $this->logger->info(static::TAG."reload $configFile with exit code $ret");
                system("$supervisorctl add $serviceName", $ret);
                $this->logger->info(static::TAG."start $serviceName with exit code $ret");
            } else {
                system("$supervisorctl start ".$serviceName, $ret);
                $this->logger->info(static::TAG."start $serviceName with exit code $ret");
            }
            pcntl_exec('/bin/sleep', [2147000000 + $this->server->getServerConfig()->getPort()->getPort()]);
        });
        if (!$ret) {
            $this->logger->error(static::TAG.'fail to obtain file lock');
        }
    }
}
