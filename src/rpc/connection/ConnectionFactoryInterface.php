<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

interface ConnectionFactoryInterface
{
    /**
     * Creates connection object.
     */
    public function create(string $servantName): ConnectionInterface;
}
