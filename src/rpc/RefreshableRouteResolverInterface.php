<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface RefreshableRouteResolverInterface extends RouteResolverInterface
{
    public function refresh(): void;
}
