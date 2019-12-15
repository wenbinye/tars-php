<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class ProxyInfo
{
    /**
     * @TarsProperty(order = 0, required = true, type = "bool")
     *
     * @var bool
     */
    public $bFromClient;
}
