<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface RouteHolderFactoryInterface
{
    public function create(string $servant): RouteHolderInterface;
}
