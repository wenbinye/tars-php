<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class RouteHolder implements RouteHolderInterface
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

    public function get(): Route
    {
        return $this->route;
    }
}
