<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\message\tup\Tup;
use wenbinye\tars\rpc\TarsRpcPacker;

class ClientRequestFactory implements ClientRequestFactoryInterface
{
    /**
     * @var MethodMetadataFactoryInterface
     */
    private $methodMetadataFactory;
    /**
     * @var TarsRpcPacker
     */
    private $tarsRpcPacker;
    /**
     * @var RequestIdGeneratorInterface
     */
    private $requestIdGenerator;
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

    public function __construct(
        MethodMetadataFactoryInterface $methodMetadataFactory,
        PackerInterface $packer,
        RequestIdGeneratorInterface $requestIdGenerator,
        int $timeout = Tup::TIMEOUT,
        int $version = Tup::VERSION,
        int $packetType = Tup::PACKET_TYPE,
        int $messageType = Tup::MESSAGE_TYPE)
    {
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->tarsRpcPacker = new TarsRpcPacker($packer);
        $this->requestIdGenerator = $requestIdGenerator;
        $this->version = $version;
        $this->packetType = $packetType;
        $this->messageType = $messageType;
        $this->timeout = $timeout;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function createRequest($servant, string $method, array $parameters): ClientRequestInterface
    {
        $methodMetadata = $this->methodMetadataFactory->create($servant, $method);
        $parameters = $this->tarsRpcPacker->packRequest($methodMetadata, $parameters, $this->version);

        $request = new Request(
            $this->requestIdGenerator->generate(),
            $servant,
            $methodMetadata,
            $parameters);
        $request->getRequestPacketBuilder()
            ->setVersion($this->version)
            ->setPacketType($this->packetType)
            ->setMessageType($this->messageType)
            ->setTimeout($this->timeout);

        return $request;
    }
}
