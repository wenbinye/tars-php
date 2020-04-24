<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

interface RefreshableServerAddressHolderInterface extends ServerAddressHolderInterface
{
    public function refresh(bool $force = false): void;
}
