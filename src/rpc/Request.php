<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Request implements ClientRequestInterface
{
    use RequestTrait;

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
     * Request constructor.
     */
    public function __construct(string $servantName,
                                string $funcName,
                                int $requestId,
                                array $parameters,
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
        $this->parameters = $parameters;
        $this->timeout = $timeout;
        $this->context = $context;
        $this->status = $status;
        $this->version = $version;
        $this->packetType = $packetType;
        $this->messageType = $messageType;
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

    public function getBody(): string
    {
        if (null === $this->body) {
            $this->body = \TUPAPI::encode(
                $this->version, $this->requestId,
                $this->servantName, $this->methodName,
                $this->packetType, $this->messageType,
                $this->timeout, $this->context, $this->status,
                self::TARS_VERSION === $this->version ? array_values($this->parameters) : $this->parameters);
        }

        return $this->body;
    }
}
