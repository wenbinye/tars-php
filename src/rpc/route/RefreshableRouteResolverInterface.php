<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use wenbinye\tars\rpc\route\RouteResolverInterface;

interface RefreshableRouteResolverInterface extends RouteResolverInterface
{
    public function refresh(bool $force = false): void;
}
