<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

trait RequestTrait
{
    /**
     * @var object
     */
    private $servant;
    /**
     * @var MethodMetadataInterface
     */
    private $methodMetadata;
    /**
     * @var int
     */
    private $requestId;

    /**
     * @var ParameterInterface[]
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
     * @var int
     */
    private $timeout;

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

    public function withVersion(int $version)
    {
        $new = clone $this;
        $new->version = $version;

        return $new;
    }

    /**
     * @return object
     */
    public function getServant()
    {
        return $this->servant;
    }

    public function getServantName(): string
    {
        return $this->methodMetadata->getServantName();
    }
    public function getFuncName(): string
    {
        return $this->methodMetadata->getMethodName();
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

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function withTimeout(int $timeout)
    {
        $new = clone $this;
        $new->timeout = $timeout;

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

    /**
     * @return MethodMetadataInterface
     */
    public function getMethod(): MethodMetadataInterface
    {
        return $this->methodMetadata;
    }
}
