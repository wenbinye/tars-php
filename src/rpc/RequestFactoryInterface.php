<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface RequestFactoryInterface
{
    public function getVersion(): int;

    public function createRequest(string $servantName, string $method, array $payload): ClientRequestInterface;
}
