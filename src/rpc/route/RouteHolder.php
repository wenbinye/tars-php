<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\RouteResolverInterface;

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
