<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\MethodMetadataInterface;
use wenbinye\tars\rpc\message\RequestTrait;
use wenbinye\tars\rpc\message\ServerRequestInterface;

class ServerRequest implements ServerRequestInterface
{
    use RequestTrait;

    /**
     * ServerRequest constructor.
     */
    public function __construct($servant, MethodMetadataInterface $methodMetadata, string $requestBody, array $parameters, int $version, int $requestId)
    {
        $this->servant = $servant;
        $this->methodMetadata = $methodMetadata;
        $this->body = $requestBody;
        $this->requestId = $requestId;
        $this->version = $version;
        $this->packetType = self::PACKET_TYPE;
        $this->messageType = self::MESSAGE_TYPE;
        $this->parameters = $parameters;
    }
}
