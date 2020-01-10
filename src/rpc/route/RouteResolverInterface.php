<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use wenbinye\tars\rpc\route\Route;

interface RouteResolverInterface
{
    public function resolve(): Route;
}
