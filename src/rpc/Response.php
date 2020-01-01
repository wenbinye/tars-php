<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Response implements ResponseInterface
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $parsedBody;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Response constructor.
     */
    public function __construct(string $body, RequestInterface $request)
    {
        $this->body = $body;
        $this->request = $request;
        $this->parsedBody = \TUPAPI::decode($body, $request->getVersion());
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getVersion(): int
    {
        return $this->request->getVersion();
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParsedBody(): array
    {
        return $this->parsedBody;
    }

    public function getReturnCode(): int
    {
        return $this->parsedBody['iRet'] ?? -1;
    }

    public function isSuccess(): bool
    {
        return 0 === $this->getReturnCode();
    }

    public function getMessage(): string
    {
        return $this->parsedBody['sResultDesc'] ?? 'Unknown error';
    }

    public function getPayload(): string
    {
        return $this->parsedBody['sBuffer'] ?? '';
    }
}
