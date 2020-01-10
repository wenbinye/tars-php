<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ResponseInterface extends MessageInterface
{
    /**
     * Retrieve the request.
     */
    public function getRequest(): RequestInterface;

    /**
     * Retrieve the response return values.
     *
     * @return ReturnValueInterface[]
     */
    public function getReturnValues(): array;

    /**
     * Return an instance with the specified return values.
     *
     * @return static
     */
    public function withReturnValues(array $returnValues);

    /**
     * Retrieve the return code.
     */
    public function getReturnCode(): int;

    /**
     * Check whether the request is success.
     *
     * @return int
     */
    public function isSuccess(): bool;

    /**
     * Retrieve the message for the return code.
     */
    public function getMessage(): string;

    /**
     * Retrieve the response payload.
     */
    public function getBody(): string;
}
