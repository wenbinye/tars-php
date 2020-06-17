<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message\tup;

use wenbinye\tars\rpc\ErrorCode;

class ResponsePacketBuilder
{
    /**
     * @var int
     */
    private $version = Tup::VERSION;
    /**
     * @var int
     */
    private $packetType = Tup::PACKET_TYPE;
    /**
     * @var int
     */
    private $requestId = -1;
    /**
     * @var int
     */
    private $messageType = Tup::MESSAGE_TYPE;
    /**
     * @var int
     */
    private $returnCode = ErrorCode::UNKNOWN;
    /**
     * @var string
     */
    private $resultDesc = '';
    /**
     * @var array
     */
    private $context = [];
    /**
     * @var array
     */
    private $status = [];

    /**
     * @var string
     */
    private $buffer = '';

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): ResponsePacketBuilder
    {
        $this->version = $version;

        return $this;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function setPacketType(int $packetType): ResponsePacketBuilder
    {
        $this->packetType = $packetType;

        return $this;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function setRequestId(int $requestId): ResponsePacketBuilder
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function setMessageType(int $messageType): ResponsePacketBuilder
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    public function setReturnCode(int $returnCode): ResponsePacketBuilder
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function setBuffer(string $buffer): ResponsePacketBuilder
    {
        $this->buffer = $buffer;

        return $this;
    }

    public function getResultDesc(): string
    {
        return $this->resultDesc;
    }

    public function setResultDesc(string $resultDesc): ResponsePacketBuilder
    {
        $this->resultDesc = $resultDesc;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): ResponsePacketBuilder
    {
        $this->context = $context;

        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): ResponsePacketBuilder
    {
        $this->status = $status;

        return $this;
    }

    public function build(): ResponsePacket
    {
        return new ResponsePacket(
            $this->version,
            $this->packetType,
            $this->requestId,
            $this->messageType,
            $this->returnCode,
            $this->buffer,
            $this->resultDesc,
            $this->context,
            $this->status
        );
    }
}
