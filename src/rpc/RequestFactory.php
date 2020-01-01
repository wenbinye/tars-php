<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class RequestFactory implements RequestFactoryInterface
{
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
    public function __construct(RequestIdGeneratorInterface $requestIdGenerator,
                                int $timeout = ClientRequestInterface::DEFAULT_TIMEOUT,
                                int $version = RequestInterface::TUP_VERSION,
                                int $packetType = RequestInterface::PACKET_TYPE,
                                int $messageType = RequestInterface::MESSAGE_TYPE)
    {
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

    public function createRequest(string $servantName, string $method, array $payload): ClientRequestInterface
    {
        return new Request($servantName, $method, $this->requestIdGenerator->generate(),
            $payload, $this->timeout, [], [],
            $this->version, $this->packetType, $this->messageType);
    }
}
