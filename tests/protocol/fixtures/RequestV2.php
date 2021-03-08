<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class RequestV2
{
    /**
     * @TarsProperty(order=0, required=true, type="long")
     *
     * @var int
     */
    public $id;
    /**
     * @TarsProperty(order=2, required=false, type="int")
     *
     * @var int
     */
    public $count;
}
