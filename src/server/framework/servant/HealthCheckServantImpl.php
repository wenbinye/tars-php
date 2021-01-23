<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework\servant;

use kuiper\di\annotation\Service;

/**
 * @Service
 */
class HealthCheckServantImpl implements HealthCheckServant
{
    public function ping(): string
    {
        return 'pong';
    }
}
