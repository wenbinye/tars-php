<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class EnumStruct
{
    /**
     * @TarsProperty(order=0, required=true, type="GoodType")
     *
     * @var GoodType
     */
    public $type;
}
