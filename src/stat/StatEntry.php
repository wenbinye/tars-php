<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\client\StatMicMsgBody;
use wenbinye\tars\client\StatMicMsgHead;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\server\ServerProperties;

class StatEntry
{
    /**
     * @var int
     */
    private $index;
    /**
     * @var StatMicMsgHead
     */
    private $head;

    /**
     * @var StatMicMsgBody
     */
    private $body;

    /**
     * StatEntry constructor.
     */
    private function __construct(int $index, StatMicMsgHead $head, StatMicMsgBody $body)
    {
        $this->index = $index;
        $this->head = $head;
        $this->body = $body;
    }

    private static function removeObj(string $servantName): string
    {
        return substr($servantName, 0, strrpos($servantName, '.'));
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getHead(): StatMicMsgHead
    {
        return $this->head;
    }

    public function getBody(): StatMicMsgBody
    {
        return $this->body;
    }

    public function getUniqueId(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return implode('|', [
            $this->index,
            $this->head->slaveName,
            $this->head->interfaceName,
            $this->head->slaveIp,
            $this->head->slavePort,
            $this->head->slaveSetName,
            $this->head->slaveSetID,
            $this->head->slaveSetArea,
            $this->head->masterName,
            $this->head->masterIp,
            $this->head->returnValue,
            $this->head->tarsVersion,
        ]);
    }

    public static function fromString(string $key): StatEntry
    {
        $head = new StatMicMsgHead();
        list(
            $index,
            $head->slaveName,
            $head->interfaceName,
            $head->slaveIp,
            $head->slavePort,
            $head->slaveSetName,
            $head->slaveSetID,
            $head->slaveSetArea,
            $head->masterName,
            $head->masterIp,
            $head->returnValue,
            $head->tarsVersion
            ) = explode('|', $key);

        return new static((int) $index, $head, new StatMicMsgBody());
    }

    public static function success(int $index, ServerProperties $serverProperties, ResponseInterface $response, int $responseTime): StatEntry
    {
        $entry = static::create($index, $serverProperties, $response, $responseTime);
        $entry->body->count = 1;

        return $entry;
    }

    public static function fail(int $index, ServerProperties $serverProperties, ResponseInterface $response, int $responseTime): StatEntry
    {
        $entry = static::create($index, $serverProperties, $response, $responseTime);
        $entry->body->execCount = 1;

        return $entry;
    }

    public static function timedOut(int $index, ServerProperties $serverProperties, ResponseInterface $response, int $responseTime): StatEntry
    {
        $entry = static::create($index, $serverProperties, $response, $responseTime);
        $entry->body->timeoutCount = 1;

        return $entry;
    }

    private static function create(int $index, ServerProperties $serverProperties, ResponseInterface $response, int $responseTime): StatEntry
    {
        $head = new StatMicMsgHead();
        $head->masterName = $serverProperties->getServerName();
        $head->masterIp = $serverProperties->getLocalIp();
        $request = $response->getRequest();
        $head->slaveName = self::removeObj($request->getServantName());
        $head->interfaceName = $request->getFuncName();
        /** @var Route $route */
        $route = $request->getAttribute('route');
        $head->slaveIp = $route->getHost();
        $head->slavePort = $route->getPort();
        $head->returnValue = $response->getReturnCode();
        $head->slaveSetName = '';
        $head->slaveSetArea = '';
        $head->slaveSetID = '';
        $head->tarsVersion = $request->getVersion();
        $body = new StatMicMsgBody();
        $body->totalRspTime = $responseTime;
        $body->minRspTime = $responseTime;
        $body->maxRspTime = $responseTime;

        return new static($index, $head, $body);
    }
}
