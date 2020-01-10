<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\TarsRpcPacker;

class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var MethodMetadataFactoryInterface
     */
    private $methodMetadataFactory;
    /**
     * @var TarsRpcPacker
     */
    private $packer;
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

    /**
     * RequestFactory constructor.
     */
    public function __construct(MethodMetadataFactoryInterface $methodMetadataFactory,
                                PackerInterface $packer,
                                RequestIdGeneratorInterface $requestIdGenerator,
                                int $timeout = RequestInterface::DEFAULT_TIMEOUT,
                                int $version = RequestInterface::TUP_VERSION,
                                int $packetType = RequestInterface::PACKET_TYPE,
                                int $messageType = RequestInterface::MESSAGE_TYPE)
    {
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->packer = new TarsRpcPacker($packer);
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

    public function createRequest($servant, string $method, array $parameters): RequestInterface
    {
        $methodMetadata = $this->methodMetadataFactory->create($servant, $method);
        $parameters = $this->packer->packRequest($methodMetadata, $parameters, $this->version);

        return new Request($servant, $methodMetadata, $this->requestIdGenerator->generate(),
            $parameters, $this->timeout, [], [],
            $this->version, $this->packetType, $this->messageType);
    }
}
