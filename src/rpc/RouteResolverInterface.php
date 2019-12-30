<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface RouteResolverInterface
{
    public function resolve(): Route;
}
