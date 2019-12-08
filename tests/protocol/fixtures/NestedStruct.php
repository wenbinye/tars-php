<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsProperty;

class NestedStruct
{
    /**
     * @TarsProperty(order=1, required=true, type="SimpleStruct")
     *
     * @var SimpleStruct
     */
    public $simpleStruct;
    /**
     * @TarsProperty(order=2, required=true, type="vector<SimpleStruct>")
     *
     * @var SimpleStruct[]
     */
    public $structList;
    /**
     * @TarsProperty(order=3, required=true, type="map<string, SimpleStruct>")
     *
     * @var array
     */
    public $structMap;
    /**
     * @TarsProperty(order=9, required=true, type="map<string, vector<SimpleStruct>>")
     *
     * @var array
     */
    public $mapOfList;
}
