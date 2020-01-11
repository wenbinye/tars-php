<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface TarsClientInterface
{
    /**
     * Call remote method.
     *
     * @param object $servant the proxy client object
     * @param string $method  the method name
     * @param mixed  ...$args the parameters
     *
     * @return array the return values
     */
    public function call($servant, string $method, ...$args): array;
}
