<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swoole\Timer;
use wenbinye\tars\report\ServerFClient;
use wenbinye\tars\report\ServerInfo;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\SwooleServer;
use wenbinye\tars\stat\StatFClient;

class ReportTaskHandler implements TaskHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ServerFClient
     */
    private $serverFClient;
    /**
     * @var SwooleServer
     */
    private $server;
    /**
     * @var ClientProperties
     */
    private $clientProperties;
    /**
     * @var StatFClient
     */
    private $statFClient;

    /**
     * KeepAliveTaskHandler constructor.
     */
    public function __construct(ClientProperties $clientProperties, SwooleServer $server, ServerFClient $serverFClient, StatFClient $statFClient)
    {
        $this->clientProperties = $clientProperties;
        $this->server = $server;
        $this->serverFClient = $serverFClient;
        $this->statFClient = $statFClient;
    }

    /**
     * @param ReportTask $task
     */
    public function handle($task): void
    {
        $this->sendServerInfo();
        Timer::tick($this->clientProperties->getKeepAliveInterval(), [$this, 'sendServerInfo']);
        $this->sendStat();
        Timer::tick($this->clientProperties->getReportInterval(), [$this, 'sendStat']);
    }

    public function sendServerInfo()
    {
        $swooleServer = $this->server->getSwooleServer();
        if (!$swooleServer) {
            return;
        }
        $serverProperties = $this->server->getServerProperties();
        $pids = $this->server->getWorkerPidList();
        if (empty($pids)) {
            $this->logger->error($serverProperties->getServerName().' all workers are gone, wait for restart');

            return;
        }
        $serverInfo = new ServerInfo();
        $serverInfo->serverName = $serverProperties->getServer();
        $serverInfo->application = $serverProperties->getApp();
        $serverInfo->pid = $swooleServer->master_pid;
        foreach ($serverProperties->getAdapters() as $adapter) {
            $serverInfo->adapter = $adapter->getAdapterName();
            $this->serverFClient->keepAlive($serverInfo);
        }
        $serverInfo->adapter = 'AdminAdapter';
        $this->serverFClient->keepAlive($serverInfo);
    }

    public function sendStat()
    {
    }
}
