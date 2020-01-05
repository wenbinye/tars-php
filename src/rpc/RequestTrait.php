<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

trait RequestTrait
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
    private $parameters = [];

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
     * @var string
     */
    private $body;

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setServantName(string $servantName): void
    {
        $this->servantName = $servantName;
    }

    public function setMethodName(string $methodName): void
    {
        $this->methodName = $methodName;
    }

    public function setRequestId(int $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function withVersion(int $version)
    {
        $new = clone $this;
        $new->version = $version;

        return $new;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function withServantName(string $servantName)
    {
        $new = clone $this;
        $new->servantName = $servantName;

        return $new;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function withMethodName(string $methodName)
    {
        $new = clone $this;
        $new->methodName = $methodName;

        return $new;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function withRequestId(int $requestId)
    {
        $new = clone $this;
        $new->requestId = $requestId;

        return $new;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return static
     */
    public function withParameters(array $parameters)
    {
        $new = clone $this;
        $new->parameters = $parameters;

        return $new;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function withPacketType(int $packetType)
    {
        $new = clone $this;
        $new->packetType = $packetType;

        return $new;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function withMessageType(int $messageType)
    {
        $new = clone $this;
        $new->messageType = $messageType;

        return $new;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function withAttribute(string $attribute, $value)
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function withBody(string $body)
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }
}
