<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface ServerAddressHolderFactoryInterface
{
    public function create(string $servant): ServerAddressHolderInterface;
}
