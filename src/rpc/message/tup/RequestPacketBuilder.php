<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message\tup;

use wenbinye\tars\rpc\message\RequestInterface;

class RequestPacketBuilder
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
    private $messageType = Tup::MESSAGE_TYPE;
    /**
     * @var int
     */
    private $requestId = 0;
    /**
     * @var string
     */
    private $servantName = '';
    /**
     * @var string
     */
    private $funcName = '';
    /**
     * @var int
     */
    private $timeout = Tup::TIMEOUT;
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

    public function setVersion(int $version): RequestPacketBuilder
    {
        $this->version = $version;

        return $this;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function setPacketType(int $packetType): RequestPacketBuilder
    {
        $this->packetType = $packetType;

        return $this;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function setMessageType(int $messageType): RequestPacketBuilder
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function setRequestId(int $requestId): RequestPacketBuilder
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function setServantName(string $servantName): RequestPacketBuilder
    {
        $this->servantName = $servantName;

        return $this;
    }

    public function getFuncName(): string
    {
        return $this->funcName;
    }

    public function setFuncName(string $funcName): RequestPacketBuilder
    {
        $this->funcName = $funcName;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): RequestPacketBuilder
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): RequestPacketBuilder
    {
        $this->context = $context;

        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): RequestPacketBuilder
    {
        $this->status = $status;

        return $this;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function setBuffer(string $buffer): RequestPacketBuilder
    {
        $this->buffer = $buffer;

        return $this;
    }

    public function build(): RequestPacket
    {
        return new RequestPacket(
            $this->version,
            $this->packetType,
            $this->messageType,
            $this->requestId,
            $this->servantName,
            $this->funcName,
            $this->buffer,
            $this->timeout,
            $this->context,
            $this->status
        );
    }

    public static function fromRequest(RequestInterface $request): RequestPacketBuilder
    {
        $builder = new self();
        $builder->setVersion($request->getVersion())
            ->setMessageType($request->getMessageType())
            ->setPacketType($request->getPacketType())
            ->setServantName($request->getServantName())
            ->setFuncName($request->getFuncName())
            ->setRequestId($request->getRequestId())
            ->setTimeout($request->getTimeout())
            ->setStatus($request->getStatus())
            ->setContext($request->getContext());

        return $builder;
    }
}
