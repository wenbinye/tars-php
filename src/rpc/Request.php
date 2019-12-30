<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Request implements RequestInterface
{
    /**
     * @var string
     */
    private $servantName;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var int
     */
    private $requestId;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var array
     */
    private $context;

    /**
     * @var array
     */
    private $status;

    /**
     * @var int
     */
    private $version;
    /**
     * @var int
     */
    private $packetType;
    /**
     * @var int
     */
    private $messageType;
    /**
     * @var array
     */
    private $attributes;

    /**
     * Request constructor.
     */
    public function __construct(string $servantName,
                                string $funcName,
                                int $requestId,
                                array $payload,
                                int $timeout = self::DEFAULT_TIMEOUT,
                                array $context = [],
                                array $status = [],
                                int $version = self::TUP_VERSION,
                                int $packetType = self::PACKET_TYPE,
                                int $messageType = self::MESSAGE_TYPE)
    {
        $this->servantName = $servantName;
        $this->methodName = $funcName;
        $this->requestId = $requestId;
        $this->payload = $payload;
        $this->timeout = $timeout;
        $this->context = $context;
        $this->status = $status;
        $this->version = $version;
        $this->packetType = $packetType;
        $this->messageType = $messageType;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function withVersion(int $version): Request
    {
        $new = clone $this;
        $new->version = $version;

        return $this;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function withServantName(string $servantName): Request
    {
        $new = clone $this;
        $new->servantName = $servantName;

        return $this;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function withMethodName(string $methodName): Request
    {
        $new = clone $this;
        $new->methodName = $methodName;

        return $this;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function withRequestId(int $requestId): Request
    {
        $new = clone $this;
        $new->requestId = $requestId;

        return $this;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function withPacketType(int $packetType): Request
    {
        $new = clone $this;
        $new->packetType = $packetType;

        return $this;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function withMessageType(int $messageType): Request
    {
        $new = clone $this;
        $new->messageType = $messageType;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function withTimeout(int $timeout): Request
    {
        $new = clone $this;
        $new->timeout = $timeout;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function withContext(array $context): Request
    {
        $new = clone $this;
        $new->context = $context;

        return $this;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function withStatus(array $status): Request
    {
        $new = clone $this;
        $new->status = $status;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function withPayload(array $payload): Request
    {
        $new = clone $this;
        $new->payload = $payload;

        return $this;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function withAttribute(string $attribute, $value): RequestInterface
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function encode(): string
    {
        return \TUPAPI::encode(
            $this->version, $this->requestId,
            $this->servantName, $this->methodName,
            $this->packetType, $this->messageType,
            $this->timeout, $this->context, $this->status,
            1 === $this->version ? array_values($this->payload) : $this->payload);
    }
}
