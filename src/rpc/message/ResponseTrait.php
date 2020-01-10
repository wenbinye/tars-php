<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\ErrorCode;

trait ResponseTrait
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var int
     */
    private $returnCode;

    /**
     * @var ReturnValueInterface[]
     */
    private $returnValues;

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

    public function getReturnCode(): int
    {
        return $this->returnCode;
    }

    public function isSuccess(): bool
    {
        return ErrorCode::SERVER_SUCCESS === $this->getReturnCode();
    }

    public function getMessage(): string
    {
        return ErrorCode::fromValue($this->getReturnCode(), ErrorCode::UNKNOWN)->message;
    }

    public function getReturnValues(): array
    {
        return $this->returnValues;
    }

    /**
     * {@inheritdoc}
     */
    public function withReturnValues(array $returnValues)
    {
        $new = clone $this;
        $new->returnValues = $returnValues;

        return $new;
    }
}
