<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\swoole\task\ProcessorInterface;
use kuiper\swoole\task\Task;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\client\ServerFServant;
use wenbinye\tars\client\ServerInfo;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\MonitorInterface;
use wenbinye\tars\stat\StatInterface;

class ReportTaskProcessor implements ProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var ServerFServant
     */
    private $serverFClient;
    /**
     * @var ServerProperties
     */
    private $serverProperties;
    /**
     * @var ClientProperties
     */
    private $clientProperties;
    /**
     * @var StatInterface
     */
    private $statClient;
    /**
     * @var MonitorInterface
     */
    private $monitor;

    /**
     * @var int
     */
    private $masterPid;

    /**
     * KeepAliveTaskHandler constructor.
     */
    public function __construct(
        ServerProperties $serverProperties,
        ClientProperties $clientProperties,
        ServerFServant $serverFClient,
        StatInterface $statClient,
        MonitorInterface $monitor,
        ?LoggerInterface $logger)
    {
        $this->clientProperties = $clientProperties;
        $this->serverFClient = $serverFClient;
        $this->statClient = $statClient;
        $this->monitor = $monitor;
        $this->serverProperties = $serverProperties;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function process(Task $task)
    {
        if (null === $this->clientProperties->getLocator()
            || null === $this->serverProperties->getNode()) {
            $this->logger->debug(static::TAG.'healthy check is disabled');

            return;
        }
        $server = $task->getServer();
        $this->masterPid = $server->getMasterPid();
        $this->sendServerInfo();
        $server->tick($this->clientProperties->getKeepAliveInterval(), function (): void {
            $this->sendServerInfo();
        });
        $server->tick($this->clientProperties->getReportInterval(), function (): void {
            $this->sendStat();
        });
        $server->tick($this->clientProperties->getReportInterval(), function (): void {
            $this->sendMonitorInfo();
        });
    }

    public function sendServerInfo(): void
    {
        $pid = $this->getServerPid();
        if (null === $pid) {
            return;
        }
        try {
            // TODO 健康检查
            $serverInfo = new ServerInfo();
            $serverInfo->serverName = $this->serverProperties->getServer();
            $serverInfo->application = $this->serverProperties->getApp();
            $serverInfo->pid = $pid;
            foreach ($this->serverProperties->getAdapters() as $adapter) {
                $serverInfo->adapter = $adapter->getAdapterName();
                $this->logger->debug(static::TAG.'send keep alive message', ['server' => $serverInfo]);
                $this->serverFClient->keepAlive($serverInfo);
            }
            $serverInfo->adapter = 'AdminAdapter';
            $this->serverFClient->keepAlive($serverInfo);
        } catch (\Exception $e) {
            $this->logger->error(static::TAG.'send server info fail', ['error' => $e->getMessage()]);
        }
    }

    public function sendStat(): void
    {
        try {
            $this->statClient->send();
        } catch (\Exception $e) {
            $this->logger->error(static::TAG.'send stat fail', ['error' => $e->getMessage()]);
        }
    }

    public function sendMonitorInfo(): void
    {
        try {
            $this->monitor->monitor();
        } catch (\Exception $e) {
            $this->logger->error(static::TAG.'send monitor fail', ['error' => $e->getMessage()]);
        }
    }

    private function getServerPid(): ?int
    {
        if ($this->serverProperties->isExternalMode()) {
            if (!file_exists($this->serverProperties->getServerPidFile())) {
                return null;
            }

            return (int) file_get_contents($this->serverProperties->getServerPidFile());
        } else {
            return $this->masterPid;
        }
    }
}
