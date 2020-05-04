<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\swoole\ServerManager;
use kuiper\swoole\task\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
     * @var ServerManager
     */
    private $serverManager;
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
                                ServerManager $serverManager, ServerFServant $serverFClient,
                                StatInterface $statClient, MonitorInterface $monitor, ?LoggerInterface $logger)
    {
        $this->clientProperties = $clientProperties;
        $this->serverManager = $serverManager;
        $this->serverFClient = $serverFClient;
        $this->statClient = $statClient;
        $this->monitor = $monitor;
        $this->serverProperties = $serverProperties;
        $this->setLogger($logger ?? new NullLogger());
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

    public function sendServerInfo($firstTime = false): void
    {
        if (!$firstTime) {
            // 首次不检查 pid，可能所有子进程还不能通过 ps 查到，可能是进程标题未修改
            $pidList = $this->serverManager->getWorkerPidList();
            if (empty($pidList)) {
                $this->logger->error(static::TAG.$this->serverProperties->getServerName().' all workers are gone, wait for restart');

                return;
            }
        }
        $serverInfo = new ServerInfo();
        $serverInfo->serverName = $this->serverProperties->getServer();
        $serverInfo->application = $this->serverProperties->getApp();
        $serverInfo->pid = $this->serverManager->getMasterPid();
        foreach ($this->serverProperties->getAdapters() as $adapter) {
            $serverInfo->adapter = $adapter->getAdapterName();
            $this->logger->info(static::TAG.'send keep alive message', ['server' => $serverInfo]);
            $this->serverFClient->keepAlive($serverInfo);
        }
        $serverInfo->adapter = 'AdminAdapter';
        $this->serverFClient->keepAlive($serverInfo);
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
}
