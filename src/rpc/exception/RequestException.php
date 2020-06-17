<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\exception;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\tup\RequestPacket;
use wenbinye\tars\rpc\message\tup\ResponsePacket;

class RequestException extends \Exception
{
    /**
     * @var RequestPacket
     */
    private $requestPacket;

    /**
     * InvalidRequestException constructor.
     */
    public function __construct(RequestPacket $requestPacket, string $message, int $code = ErrorCode::UNKNOWN)
    {
        parent::__construct($message, $code);
        $this->requestPacket = $requestPacket;
    }

    public function getRequestPacket(): RequestPacket
    {
        return $this->requestPacket;
    }

    public function toResponseBody(): string
    {
        $requestPacket = $this->getRequestPacket();

        return ResponsePacket::builder()
            ->setRequestId($requestPacket->getRequestId())
            ->setVersion($requestPacket->getVersion())
            ->setPacketType($requestPacket->getPacketType())
            ->setMessageType($requestPacket->getMessageType())
            ->setReturnCode($this->getCode())
            ->setResultDesc($this->getMessage())
            ->build()
            ->pack($requestPacket->getServantName(), $requestPacket->getFuncName(), []);
    }
}
