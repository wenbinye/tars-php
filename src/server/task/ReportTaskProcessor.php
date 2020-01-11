<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\swoole\SwooleServer;
use kuiper\swoole\task\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swoole\Timer;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\MonitorInterface;
use wenbinye\tars\stat\ServerFServant;
use wenbinye\tars\stat\ServerInfo;
use wenbinye\tars\stat\StatInterface;

class ReportTaskProcessor implements ProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ServerFServant
     */
    private $serverFClient;
    /**
     * @var ServerProperties
     */
    private $serverProperties;
    /**
     * @var SwooleServer
     */
    private $server;
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
     * KeepAliveTaskHandler constructor.
     */
    public function __construct(ServerProperties $serverProperties, ClientProperties $clientProperties,
                                SwooleServer $server, ServerFServant $serverFClient,
                                StatInterface $statClient, MonitorInterface $monitor)
    {
        $this->clientProperties = $clientProperties;
        $this->server = $server;
        $this->serverFClient = $serverFClient;
        $this->statClient = $statClient;
        $this->monitor = $monitor;
        $this->serverProperties = $serverProperties;
    }

    /**
     * @param ReportTask $task
     */
    public function process($task): void
    {
        $this->sendServerInfo();
        Timer::tick($this->clientProperties->getKeepAliveInterval(), function () {
            $this->sendServerInfo();
        });
        $this->sendStat();
        Timer::tick($this->clientProperties->getReportInterval(), function () {
            $this->sendStat();
        });
        $this->sendMonitorInfo();
        Timer::tick($this->clientProperties->getReportInterval(), function () {
            $this->sendMonitorInfo();
        });
    }

    public function sendServerInfo()
    {
        $swooleServer = $this->server->getSwooleServer();
        if (!$swooleServer) {
            return;
        }
        $pids = $this->server->getWorkerPidList();
        if (empty($pids)) {
            $this->logger->error($this->server->getServerConfig()->getServerName().' all workers are gone, wait for restart');

            return;
        }
        $serverInfo = new ServerInfo();
        $serverInfo->serverName = $this->serverProperties->getServer();
        $serverInfo->application = $this->serverProperties->getApp();
        $serverInfo->pid = $swooleServer->master_pid;
        foreach ($this->serverProperties->getAdapters() as $adapter) {
            $serverInfo->adapter = $adapter->getAdapterName();
            $this->serverFClient->keepAlive($serverInfo);
        }
        $serverInfo->adapter = 'AdminAdapter';
        $this->serverFClient->keepAlive($serverInfo);
    }

    public function sendStat()
    {
        $this->statClient->send();
    }

    public function sendMonitorInfo()
    {
        $this->monitor->monitor();
    }
}
