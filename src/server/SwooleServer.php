<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server;
use Symfony\Component\Console\Output\OutputInterface;
use wenbinye\tars\server\event\SwooleServerEventFactory;

class SwooleServer implements ServerInterface
{
    const MASTER_PROCESS_NAME = 'master';
    const MANAGER_PROCESS_NAME = 'manager';
    const WORKER_PROCESS_NAME = 'worker';

    /**
     * @var ServerProperties
     */
    private $serverProperties;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Server
     */
    private $swooleServer;

    /**
     * @var SwooleServerEventFactory
     */
    private $swooleServerEventFactory;

    /**
     * SwooleServer constructor.
     */
    public function __construct(ServerProperties $serverProperties, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->serverProperties = $serverProperties;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->swooleServerEventFactory = new SwooleServerEventFactory($this);
    }

    /**
     * {@inheritdoc}
     */
    public function start(): void
    {
        $adapters = $this->serverProperties->getAdapters();
        $this->createSwooleServer(array_shift($adapters));
        foreach ($adapters as $adapter) {
            $this->addPort($adapter);
        }
        $this->swooleServer->start();
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        $pids = $this->getAllPids();
        if (empty($pids)) {
            $this->output->writeln('<info>Server was not started</info>');

            return;
        }
        exec('kill -9 '.implode(' ', $pids), $output, $ret);
        if (0 === $ret) {
            $this->output->writeln('<info>[SUCCESS]</info>');
        } else {
            $this->output->writeln('<error>Server was failed to stop</error>');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getServerProperties(): ServerProperties
    {
        return $this->serverProperties;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function getSwooleServer(): Server
    {
        return $this->swooleServer;
    }

    public function getTaskWorkerNum()
    {
        return $this->serverProperties->getSwooleServerSettings()[SwooleServerSetting::TASK_WORKER_NUM] ?? 0;
    }

    public function getWorkerNum()
    {
        return $this->serverProperties->getSwooleServerSettings()[SwooleServerSetting::WORKER_NUM] ?? 0;
    }

    public function getTotalWorkerNum()
    {
        return $this->getTaskWorkerNum() + $this->getWorkerNum();
    }

    public function getAllPids()
    {
        $pids[] = $this->getMasterPid();
        $pids[] = $this->getManagerPid();
        $pids = array_merge($pids, $this->getWorkerPidList());

        return array_filter($pids);
    }

    public function getMasterPid()
    {
        return current($this->getPidListByType(self::MASTER_PROCESS_NAME));
    }

    public function getManagerPid()
    {
        return current($this->getPidListByType(self::MANAGER_PROCESS_NAME));
    }

    public function getWorkerPidList()
    {
        return $this->getPidListByType(self::WORKER_PROCESS_NAME);
    }

    private function getPidListByType(string $processType): array
    {
        exec(sprintf("ps aux | grep %s | grep %s | grep -v grep | awk '{print $2}'",
            $this->serverProperties->getServerName(), $processType), $pids);

        return array_map('intval', $pids);
    }

    private function swooleEventHandler(string $eventName)
    {
        return function () use ($eventName) {
            $event = $this->swooleServerEventFactory->create($eventName, func_get_args());
            if ($event) {
                $this->logger->debug("create $eventName event ".get_class($event));
                $this->eventDispatcher->dispatch($event);
            } else {
                $this->logger->debug("no event handler for $eventName");
            }
        };
    }

    private function createSwooleServer(AdapterProperties $adapter): void
    {
        $serverType = SwooleServerType::fromValue($adapter->getSwooleServerType());
        $swooleServerClass = $serverType->server;
        $listen = $adapter->getEndpoint();
        $this->swooleServer = new $swooleServerClass($listen->getHost(), $listen->getPort(), SWOOLE_PROCESS, $adapter->getSwooleSockType());
        $this->swooleServer->set(array_merge($this->serverProperties->getSwooleServerSettings(), $serverType->settings));

        foreach (SwooleEvent::values() as $event) {
            if (in_array($event, SwooleEvent::requestEvents(), true)) {
                continue;
            }
            $this->logger->debug("attach $event to server");

            $this->swooleServer->on($event, $this->swooleEventHandler($event));
        }

        foreach ($serverType->events as $event) {
            $this->logger->debug("attach $event to server");
            $this->swooleServer->on($event, $this->swooleEventHandler($event));
        }
    }

    private function addPort(AdapterProperties $adapter): void
    {
        $serverType = SwooleServerType::fromValue($adapter->getSwooleServerType());
        $listen = $adapter->getEndpoint();
        /** @var Server\Port $port */
        $port = $this->swooleServer->addlistener($listen->getHost(), $listen->getPort(), $adapter->getSwooleSockType());
        $port->set($serverType->settings);

        foreach ($serverType->events as $event) {
            $this->logger->debug("attach $event to port ".$listen->getPort());
            $port->on($event, $this->swooleEventHandler($event));
        }
    }
}
