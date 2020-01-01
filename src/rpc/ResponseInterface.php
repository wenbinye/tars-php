<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ResponseInterface extends MessageInterface
{
    public function getRequest(): RequestInterface;

    public function getParsedBody(): array;

    public function getReturnCode(): int;

    public function isSuccess(): bool;

    public function getMessage(): string;

    public function getPayload(): string;
}
