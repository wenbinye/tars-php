<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface ServerAddressHolderInterface
{
    public function get(): ServerAddress;
}
