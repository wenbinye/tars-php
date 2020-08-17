<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface ServerAddressHolderInterface
{
    /**
     * Gets the address.
     *
     * @return ServerAddress
     */
    public function get(): ServerAddress;
}
