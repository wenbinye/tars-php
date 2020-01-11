<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface RouteHolderInterface
{
    public function get(): Route;
}
