<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\rpc\RequestTrait;

class ServerRequest implements ServerRequestInterface
{
    use RequestTrait;

    /**
     * @var string
     */
    private $payload;

    /**
     * ServerRequest constructor.
     */
    public function __construct(string $body)
    {
        $this->body = $body;
        $unpackResult = \TUPAPI::decodeReqPacket($body);
        $this->requestId = $unpackResult['iRequestId'];
        $this->version = $unpackResult['iVersion'];
        $this->servantName = $unpackResult['sServantName'];
        $this->methodName = $unpackResult['sFuncName'];
        $this->payload = $unpackResult['sBuffer'];
        $this->packetType = self::PACKET_TYPE;
        $this->messageType = self::MESSAGE_TYPE;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }
}
