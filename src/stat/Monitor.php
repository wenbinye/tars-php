<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\client\PropertyFServant;
use wenbinye\tars\client\StatPropInfo;
use wenbinye\tars\client\StatPropMsgBody;
use wenbinye\tars\client\StatPropMsgHead;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\collector\CollectorInterface;

class Monitor implements MonitorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var PropertyFServant
     */
    private $propertyFClient;
    /**
     * @var CollectorInterface[]
     */
    private $collectors;
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    public function __construct(ServerProperties $serverProperties, PropertyFServant $propertyFClient, array $collectors, ?LoggerInterface $logger)
    {
        $this->propertyFClient = $propertyFClient;
        $this->collectors = $collectors;
        $this->serverProperties = $serverProperties;
        $this->setLogger($logger ?? new NullLogger());
    }

    public function monitor(): void
    {
        $msg = new StructMap();
        foreach ($this->collectors as $collector) {
            foreach ($collector->getValues() as $name => $value) {
                $msg->put($this->createHead($name), $this->createBody($collector->getPolicy(), (string) $value));
            }
        }
        $this->logger->debug(static::TAG.'send properties', ['msg' => $msg]);
        $this->propertyFClient->reportPropMsg($msg);
    }

    public function createHead(string $propertyName): StatPropMsgHead
    {
        $propMsgHead = new StatPropMsgHead();
        $propMsgHead->moduleName = $this->serverProperties->getServerName();
        $propMsgHead->ip = $this->serverProperties->getLocalIp();
        $propMsgHead->propertyName = $propertyName;
        $propMsgHead->iPropertyVer = 1;

        return $propMsgHead;
    }

    /**
     * @param string $policy
     * @param string $value
     *
     * @return StatPropMsgBody
     */
    private function createBody(string $policy, string $value): StatPropMsgBody
    {
        $propMsgBody = new StatPropMsgBody();
        $propInfo = new StatPropInfo();
        $propInfo->policy = $policy;
        $propInfo->value = $value;
        $propMsgBody->vInfo = [$propInfo];

        return $propMsgBody;
    }
}
