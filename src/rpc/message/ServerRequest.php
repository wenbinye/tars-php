<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\tup\RequestPacket;

class ServerRequest extends AbstractRequest implements ServerRequestInterface
{
    /**
     * @var RequestPacket
     */
    private $requestPacket;

    public function __construct(
        $servant,
        MethodMetadataInterface $methodMetadata,
        RequestPacket $requestPacket,
        array $parameters)
    {
        $this->servant = $servant;
        $this->methodMetadata = $methodMetadata;
        $this->requestPacket = $requestPacket;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->requestPacket->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketType(): int
    {
        return $this->requestPacket->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType(): int
    {
        return $this->requestPacket->getMessageType();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestId(): int
    {
        return $this->requestPacket->getRequestId();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        return $this->requestPacket->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return $this->requestPacket->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        return $this->requestPacket->getBuffer();
    }

    /**
     * {@inheritdoc}
     */
    public function getServantName(): string
    {
        return $this->requestPacket->getServantName();
    }

    /**
     * {@inheritdoc}
     */
    public function getFuncName(): string
    {
        return $this->requestPacket->getFuncName();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(): int
    {
        return $this->requestPacket->getTimeout();
    }
}
