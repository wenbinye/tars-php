<?php

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsStructProperty;

class SimpleStruct
{
    /**
     * @TarsStructProperty(order=0, required=true, type="long")
     *
     * @var int
     */
    public $id;
    /**
     * @TarsStructProperty(order=1, required=true, type="int")
     *
     * @var int
     */
    public $count;
    /**
     * @TarsStructProperty(order=2, required=true, type="short")
     *
     * @var int
     */
    public $page = 1;
}
