<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class RequestV1
{
    /**
     * @TarsProperty(order=0, required=true, type="long")
     *
     * @var int
     */
    public $id;
    /**
     * @TarsProperty(order=1, required=false, type="short")
     *
     * @var int
     */
    public $page = 1;
}
