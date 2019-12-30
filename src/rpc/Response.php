<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Response implements ResponseInterface
{
    /**
     * @var string
     */
    private $rawContent;

    /**
     * @var array
     */
    private $decoded;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Response constructor.
     */
    public function __construct(string $rawContent, RequestInterface $request)
    {
        $this->rawContent = $rawContent;
        $this->request = $request;
        $this->decoded = \TUPAPI::decode($rawContent, $request->getVersion());
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getRawContent(): string
    {
        return $this->rawContent;
    }

    public function getDecoded(): array
    {
        return $this->decoded;
    }

    public function getReturnCode(): int
    {
        return $this->decoded['iRet'] ?? -1;
    }

    public function isSuccess(): bool
    {
        return 0 === $this->getReturnCode();
    }

    public function getErrorMessage(): string
    {
        return $this->decoded['sResultDesc'] ?? 'Unknown error';
    }

    public function getPayload(): string
    {
        return $this->decoded['sBuffer'] ?? '';
    }
}
