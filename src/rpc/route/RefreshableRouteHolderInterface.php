<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface RefreshableRouteHolderInterface extends RouteHolderInterface
{
    public function refresh(bool $force = false): void;
}
