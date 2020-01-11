<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

interface ConnectionFactoryInterface
{
    public function has(string $servantName): bool;

    public function create(string $servantName): ConnectionInterface;
}
