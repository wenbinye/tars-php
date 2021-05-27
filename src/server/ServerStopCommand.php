<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\event\ServerEventFactory;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends AbstractServerCommand
{
    protected const TAG = '['.__CLASS__.'] ';

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
     * @var ServerEventFactory
     */
    private $serverEventFactory;

    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ServerStartCommand constructor.
     *
     * @param ServerManager            $serverManager
     * @param ServerProperties         $serverProperties
     * @param ServerInterface          $server
     * @param ServerEventFactory       $serverEventFactory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ServerManager $serverManager,
        ServerProperties $serverProperties,
        ServerInterface $server,
        ServerEventFactory $serverEventFactory,
        EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->serverManager = $serverManager;
        $this->serverProperties = $serverProperties;
        $this->server = $server;
        $this->serverEventFactory = $serverEventFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this->setDescription('stop php server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->serverProperties->isExternalMode()) {
            $this->stopService($input);
            if (file_exists($this->serverProperties->getServerPidFile())) {
                $pid = (int) file_get_contents($this->serverProperties->getServerPidFile());
                if (function_exists('posix_kill')) {
                    posix_kill($pid, SIGTERM);
                } else {
                    exec("kill -TERM $pid");
                }
                @unlink($this->serverProperties->getServerPidFile());
            }
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
        $this->withFileLock($configFile, function () use ($serviceName, $configFile) {
            $shutdownEvent = $this->serverEventFactory->create('shutdown', [$this->server]);
            if (null !== $shutdownEvent) {
                $this->eventDispatcher->dispatch($shutdownEvent);
            }
            $supervisorctl = $this->serverProperties->getSupervisorctl() ?? 'supervisorctl';
            system("$supervisorctl stop ".$serviceName, $ret);
            $this->logger->info(static::TAG."stop $serviceName with exit code $ret");

            system("$supervisorctl remove ".$serviceName, $ret);
            $this->logger->info(static::TAG."remove $serviceName with exit code $ret");

            if (file_exists($configFile)) {
                $this->logger->info(static::TAG."remove supervisor config $configFile");
                @rename($configFile, $configFile.'.disabled');
            }
        });
    }
}
