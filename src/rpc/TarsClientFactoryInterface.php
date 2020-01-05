<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface TarsClientFactoryInterface
{
    public function create(string $clientClassName);
}
