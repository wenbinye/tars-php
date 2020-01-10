<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface TarsClientFactoryInterface
{
    /**
     * Creates servant client object.
     *
     * @param string $clientClassName the servant interface class name
     *
     * @return object
     */
    public function create(string $clientClassName);
}
