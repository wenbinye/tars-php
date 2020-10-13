<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\tup\RequestPacket;
use wenbinye\tars\rpc\message\tup\RequestPacketBuilder;

class Request extends AbstractRequest implements ClientRequestInterface
{
    /**
     * @var RequestPacketBuilder
     */
    private $requestPacketBuilder;

    /**
     * Request constructor.
     *
     * @param int                     $requestId
     * @param object                  $servant
     * @param MethodMetadataInterface $methodMetadata
     * @param array                   $parameters
     */
    public function __construct(
        int $requestId,
        $servant,
        MethodMetadataInterface $methodMetadata,
        array $parameters)
    {
        $this->servant = $servant;
        $this->methodMetadata = $methodMetadata;
        $this->parameters = $parameters;
        $this->requestPacketBuilder = RequestPacket::builder();
        $this->requestPacketBuilder->setRequestId($requestId)
            ->setServantName($methodMetadata->getServantName())
            ->setFuncName($methodMetadata->getMethodName());
    }

    private function packParameters(): array
    {
        $ret = [];
        /** @var ParameterInterface $parameter */
        foreach ($this->parameters as $parameter) {
            $key = $this->isCurrentVersion() ? $parameter->getName() : $parameter->getOrder();
            $ret[$key] = $parameter->getPayload();
        }

        return $ret;
    }

    public function getBody(): string
    {
        return \TUPAPI::encode(
            $this->requestPacketBuilder->getVersion(),
            $this->requestPacketBuilder->getRequestId(),
            $this->requestPacketBuilder->getServantName(),
            $this->requestPacketBuilder->getFuncName(),
            $this->requestPacketBuilder->getPacketType(),
            $this->requestPacketBuilder->getMessageType(),
            $this->requestPacketBuilder->getTimeout(),
            $this->requestPacketBuilder->getContext(),
            $this->requestPacketBuilder->getStatus(),
            $this->packParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->requestPacketBuilder->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketType(): int
    {
        return $this->requestPacketBuilder->getPacketType();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType(): int
    {
        return $this->requestPacketBuilder->getMessageType();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestId(): int
    {
        return $this->requestPacketBuilder->getRequestId();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        return $this->requestPacketBuilder->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return $this->requestPacketBuilder->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getServantName(): string
    {
        return $this->requestPacketBuilder->getServantName();
    }

    /**
     * {@inheritdoc}
     */
    public function getFuncName(): string
    {
        return $this->requestPacketBuilder->getFuncName();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout(): int
    {
        return $this->requestPacketBuilder->getTimeout();
    }

    public function getRequestPacketBuilder(): RequestPacketBuilder
    {
        return $this->requestPacketBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function withContext(array $context)
    {
        $new = clone $this;
        $new->requestPacketBuilder = clone $this->requestPacketBuilder;
        $new->requestPacketBuilder->setContext($context);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus(array $status)
    {
        $new = clone $this;
        $new->requestPacketBuilder = clone $this->requestPacketBuilder;
        $new->requestPacketBuilder->setStatus($status);

        return $new;
    }
}
