<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swoole\Server;
use Symfony\Component\Console\Output\OutputInterface;

class SwooleServer implements ServerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    const MASTER_PROCESS_NAME = 'master';
    const MANAGER_PROCESS_NAME = 'manager';
    const WORKER_PROCESS_NAME = 'worker';

    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Server
     */
    private $swooleServer;

    /**
     * SwooleServer constructor.
     */
    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    public function start(): void
    {
        $adapters = $this->serverProperties->getAdapters();
        $this->createSwooleServer(array_shift($adapters));
        foreach ($adapters as $adapter) {
            $this->addPort($adapter);
        }
    }

    public function stop(): void
    {
        $pids = $this->getPidList();
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

    private function getPidList()
    {
        $pids[] = $this->getMasterPid();
        $pids[] = $this->getManagerPid();
        $pids = array_merge($pids, $this->getWorkerPidList());

        return array_filter($pids);
    }

    private function getMasterPid()
    {
        return current($this->getPidListByType(self::MASTER_PROCESS_NAME));
    }

    private function getManagerPid()
    {
        return current($this->getPidListByType(self::MANAGER_PROCESS_NAME));
    }

    private function getWorkerPidList()
    {
        return $this->getPidListByType(self::WORKER_PROCESS_NAME);
    }

    private function getPidListByType(string $processType): array
    {
        exec(sprintf("ps aux | grep %s | grep %s | grep -v grep | awk '{print $2}'",
            $this->serverProperties->getServerName(), $processType), $pids);

        return array_map('intval', $pids);
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    private function createSwooleServer(AdapterProperties $adapter): void
    {
        $serverType = SwooleServerType::fromValue($adapter->getSwooleServerType());
        $swooleServerClass = $serverType->server;
        $listen = $adapter->getEndpoint();
        $this->swooleServer = new $swooleServerClass($listen->getHost(), $listen->getPort(), SWOOLE_PROCESS, $adapter->getSwooleSockType());
        $this->swooleServer->set(array_merge($this->serverProperties->getSwooleServerProperties(), $serverType->settings));
    }

    private function addPort(AdapterProperties $adapter): void
    {
        $serverType = SwooleServerType::fromValue($adapter->getSwooleServerType());
        $listen = $adapter->getEndpoint();
        /** @var Server\Port $port */
        $port = $this->swooleServer->addlistener($listen->getHost(), $listen->getPort(), $adapter->getSwooleSockType());
        $port->set($serverType->settings);
    }
}
