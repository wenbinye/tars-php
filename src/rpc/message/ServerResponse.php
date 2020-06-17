<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\tup\ResponsePacket;
use wenbinye\tars\rpc\message\tup\ResponsePacketBuilder;

class ServerResponse extends AbstractResponse
{
    /**
     * @var ResponsePacketBuilder
     */
    private $responsePacketBuilder;

    public function __construct(RequestInterface $request, array $returnValues)
    {
        $this->responsePacketBuilder = ResponsePacket::builder();
        $this->request = $request;
        $this->responsePacketBuilder->setReturnCode(ErrorCode::SERVER_SUCCESS)
            ->setRequestId($request->getRequestId())
            ->setVersion($request->getVersion())
            ->setPacketType($request->getPacketType())
            ->setMessageType($request->getMessageType());
        $this->returnValues = $returnValues;
    }

    public function getBody(): string
    {
        return $this->responsePacketBuilder->build()->pack(
            $this->request->getServantName(), $this->request->getFuncName(), $this->packReturnValues());
    }

    private function packReturnValues(): array
    {
        $ret = [];
        foreach ($this->returnValues as $returnValue) {
            $ret[$returnValue->getName() ?? ''] = $returnValue->getPayload();
        }

        return $this->isCurrentVersion() ? $ret : array_values($ret);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->responsePacketBuilder->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketType(): int
    {
        return $this->responsePacketBuilder->getPacketType();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType(): int
    {
        return $this->responsePacketBuilder->getMessageType();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestId(): int
    {
        return $this->responsePacketBuilder->getRequestId();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        return $this->responsePacketBuilder->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return $this->responsePacketBuilder->getContext();
    }

    public function getResponsePacketBuilder(): ResponsePacketBuilder
    {
        return $this->responsePacketBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnCode(): int
    {
        return $this->responsePacketBuilder->getReturnCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return $this->responsePacketBuilder->getResultDesc();
    }
}
