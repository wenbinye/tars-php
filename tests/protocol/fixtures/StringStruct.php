<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class StringStruct
{
    /**
     * @TarsProperty(order=0, required=true, type="string")
     *
     * @var string
     */
    public $name;
}
