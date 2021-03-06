<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\swoole\ServerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const COMMAND_NAME = 'stop';

    /**
     * @var ServerManager
     */
    private $serverManager;

    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * ServerStartCommand constructor.
     *
     * @param ServerManager    $serverManager
     * @param ServerProperties $serverProperties
     */
    public function __construct(ServerManager $serverManager, ServerProperties $serverProperties)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->serverManager = $serverManager;
        $this->serverProperties = $serverProperties;
    }

    protected function configure(): void
    {
        $this->setDescription('stop php server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->serverProperties->isExternalMode()
            && file_exists($this->serverProperties->getServerPidFile())) {
            $this->stopService($input);
            $pid = (int) file_get_contents($this->serverProperties->getServerPidFile());
            if (function_exists('posix_kill')) {
                posix_kill($pid, SIGTERM);
            } else {
                exec("kill -TERM $pid");
            }
            unlink($this->serverProperties->getServerPidFile());
        } else {
            $this->serverManager->stop();
        }

        return 0;
    }

    private function stopService(InputInterface $input): void
    {
        $confPath = $this->serverProperties->getSupervisorConfPath();
        if (null === $confPath || !is_dir($confPath)) {
            throw new \RuntimeException('tars.application.server.supervisor_conf_path cannot be empty when start_mode is external');
        }
        $serviceName = $this->serverProperties->getServerName();
        $configFile = $confPath.'/'.$serviceName.$this->serverProperties->getSupervisorConfExtension();
        @unlink($configFile);
        $supervisorctl = $this->serverProperties->getSupervisorctl() ?? 'supervisorctl';
        system("$supervisorctl update ".$serviceName);
    }
}
