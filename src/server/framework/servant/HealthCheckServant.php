<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework\servant;

use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant("HealthCheckObj")
 */
interface HealthCheckServant
{
    /**
     * @TarsReturnType(type = "string")
     *
     * @return string
     */
    public function ping(): string;
}
