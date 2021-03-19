<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\server\SwooleServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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

    private function startService(InputInterface $input): void
    {
        $confPath = $this->serverProperties->getSupervisorConfPath();
        if (null === $confPath || !is_dir($confPath)) {
            throw new \RuntimeException('tars.application.server.supervisor_conf_path cannot be empty when start_mode is external');
        }
        $serviceName = $this->serverProperties->getServerName();
        $configFile = $confPath.'/'.$serviceName.$this->serverProperties->getSupervisorConfExtension();
        $configContent = strtr('[program:{server_name}]
directory={cwd}
command={php} {script_file} --config={conf_file} start --server > {log_file} 2>&1
startsecs=5
', [
            '{cwd}' => getcwd(),
            '{server_name}' => $serviceName,
            '{php}' => PHP_BINARY,
            '{script_file}' => realpath($_SERVER['SCRIPT_FILENAME']),
            '{log_file}' => $this->serverProperties->getAppLogPath().'/'.$serviceName.'.log',
            '{conf_file}' => realpath(ServerApplication::getInstance()->getConfigFile()),
        ]);
        $supervisorctl = $this->serverProperties->getSupervisorctl() ?? 'supervisorctl';
        if (!file_exists($configFile) || file_get_contents($configFile) !== $configContent) {
            file_put_contents($configFile, $configContent);
            system("$supervisorctl update ".$serviceName);
        } else {
            system("$supervisorctl start ".$serviceName);
        }
        pcntl_exec('/bin/sleep', [2147000000 + $this->server->getServerConfig()->getPort()->getPort()]);
    }
}
