<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface ServerAddressHolderFactoryInterface
{
    /**
     * Creates ServerAddressHolder object.
     */
    public function create(string $servant): ServerAddressHolderInterface;
}
