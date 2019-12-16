<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class StatPropMsgBody
{
    /**
     * @TarsProperty(order = 0, required = true, type = "vector<StatPropInfo>")
     *
     * @var array
     */
    public $vInfo;
}
