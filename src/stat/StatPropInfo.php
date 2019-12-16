<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class StatPropInfo
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $policy;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     *
     * @var string
     */
    public $value;
}
