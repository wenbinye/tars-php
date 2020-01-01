<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ConnectionFactoryInterface
{
    public function create(string $servantName): ConnectionInterface;
}
