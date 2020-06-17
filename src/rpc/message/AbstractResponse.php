<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\tup\Tup;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ReturnValueInterface[]
     */
    protected $returnValues;

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function isCurrentVersion(): bool
    {
        return $this->getVersion() === Tup::VERSION;
    }

    public function isSuccess(): bool
    {
        return ErrorCode::SERVER_SUCCESS === $this->getReturnCode();
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
