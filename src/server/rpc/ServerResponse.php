<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\RequestInterface;
use wenbinye\tars\rpc\ResponseInterface;

class ServerResponse implements ResponseInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $parsedBody;
    /**
     * @var int
     */
    private $returnCode;
    /**
     * @var string
     */
    private $message;

    public function __construct(ServerRequestInterface $request, array $parsedBody, int $returnCode, string $message = null)
    {
        $this->request = $request;
        $this->parsedBody = $parsedBody;
        $this->returnCode = $returnCode;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->request->getVersion();
    }

    public function getBody(): string
    {
        if (null === $this->body) {
            $this->body = \TUPAPI::encodeRspPacket($this->getVersion(),
                $this->request->getPacketType(), $this->request->getMessageType(), $this->request->getRequestId(),
                $this->getReturnCode(), $this->getMessage(), $this->getParsedBody(), []);
        }

        return $this->body;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    public function isSuccess(): bool
    {
        return ErrorCode::SERVER_SUCCESS === $this->returnCode;
    }

    public function getMessage(): string
    {
        if (!isset($this->message)) {
            $this->message = ErrorCode::fromValue($this->returnCode, ErrorCode::fromValue(ErrorCode::UNKNOWN))->message;
        }

        return $this->message;
    }

    public function getPayload(): string
    {
        return $this->getBody();
    }
}
