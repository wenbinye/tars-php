<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class RouteHolder implements RouteResolverInterface
{
    /**
     * @var Route
     */
    private $route;

    /**
     * RouteHolder constructor.
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function resolve(): Route
    {
        return $this->route;
    }
}
