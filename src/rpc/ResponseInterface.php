<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ResponseInterface
{
    public function getRequest(): RequestInterface;

    public function getRawContent(): string;

    public function getDecoded(): array;

    public function getReturnCode(): int;

    public function isSuccess(): bool;

    public function getErrorMessage(): string;

    public function getPayload(): string;
}
