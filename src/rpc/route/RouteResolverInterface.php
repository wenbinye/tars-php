<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface RouteResolverInterface
{
    /**
     * Resolve servant route.
     *
     * @return Route[]
     */
    public function resolve(string $servantName): array;
}
