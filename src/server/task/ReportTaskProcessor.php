<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\swoole\SwooleServer;
use kuiper\swoole\task\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swoole\Timer;
use wenbinye\tars\client\ServerFServant;
use wenbinye\tars\client\ServerInfo;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\MonitorInterface;
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
        $this->sendServerInfo(true);
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

    public function sendServerInfo($firstTime = false)
    {
        $swooleServer = $this->server->getSwooleServer();
        if (!$swooleServer) {
            return;
        }
        if (!$firstTime) {
            // 首次不检查 pid，可能所有子进程还不能通过 ps 查到，可能是进程标题未修改
            $pids = $this->server->getWorkerPidList();
            if (empty($pids)) {
                $this->logger->error('[ReportTaskProcessor] '.$this->server->getServerConfig()->getServerName().' all workers are gone, wait for restart');

                return;
            }
        }
        $serverInfo = new ServerInfo();
        $serverInfo->serverName = $this->serverProperties->getServer();
        $serverInfo->application = $this->serverProperties->getApp();
        $serverInfo->pid = $swooleServer->master_pid;
        foreach ($this->serverProperties->getAdapters() as $adapter) {
            $serverInfo->adapter = $adapter->getAdapterName();
            $this->logger->info('[ReportTaskProcessor] send keep alive message', ['server' => $serverInfo]);
            $this->serverFClient->keepAlive($serverInfo);
        }
        $serverInfo->adapter = 'AdminAdapter';
        $this->serverFClient->keepAlive($serverInfo);
    }

    public function sendStat()
    {
        try {
            $this->statClient->send();
        } catch (\Exception $e) {
            $this->logger && $this->logger->error('[Stat] send stat fail', ['error' => $e->getMessage()]);
        }
    }

    public function sendMonitorInfo()
    {
        try {
            $this->monitor->monitor();
        } catch (\Exception $e) {
            $this->logger && $this->logger->error('[Stat] send monitor fail', ['error' => $e->getMessage()]);
        }
    }
}
