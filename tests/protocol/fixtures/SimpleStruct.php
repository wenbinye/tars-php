<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class SimpleStruct
{
    /**
     * @TarsProperty(order=0, required=true, type="long")
     *
     * @var int
     */
    public $id;
    /**
     * @TarsProperty(order=1, required=true, type="int")
     *
     * @var int
     */
    public $count;
    /**
     * @TarsProperty(order=2, required=true, type="short")
     *
     * @var int
     */
    public $page = 1;
}
