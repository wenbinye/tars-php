<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class Request implements RequestInterface
{
    use RequestTrait;

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
    public function __construct($servant, MethodMetadataInterface $methodMetadata,
                                int $requestId,
                                array $parameters,
                                int $timeout = self::DEFAULT_TIMEOUT,
                                array $context = [],
                                array $status = [],
                                int $version = self::TUP_VERSION,
                                int $packetType = self::PACKET_TYPE,
                                int $messageType = self::MESSAGE_TYPE)
    {
        $this->servant = $servant;
        $this->methodMetadata = $methodMetadata;
        $this->requestId = $requestId;
        $this->parameters = $parameters;
        $this->timeout = $timeout;
        $this->context = $context;
        $this->status = $status;
        $this->version = $version;
        $this->packetType = $packetType;
        $this->messageType = $messageType;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function withContext(array $context)
    {
        $new = clone $this;
        $new->context = $context;

        return $new;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function withStatus(array $status)
    {
        $new = clone $this;
        $new->status = $status;

        return $new;
    }

    private function getParameterArray(): array
    {
        $ret = [];
        /** @var ParameterInterface $parameter */
        foreach ($this->parameters as $parameter) {
            $ret[$this->version === self::TARS_VERSION ? $parameter->getOrder() : $parameter->getName()] = $parameter->getPayload();
        }
        return $ret;
    }

    public function getBody(): string
    {
        return \TUPAPI::encode(
            $this->version, $this->requestId,
            $this->getServantName(), $this->getFuncName(),
            $this->packetType, $this->messageType,
            $this->timeout, $this->context, $this->status,
            $this->getParameterArray());
    }
}
