<?php

namespace wenbinye\tars\protocol\fixtures;

use wenbinye\tars\protocol\annotation\TarsStructProperty;

class NestedStruct
{
    /**
     * @TarsStructProperty(order=1, required=true, type="SimpleStruct")
     *
     * @var SimpleStruct
     */
    public $simpleStruct;
    /**
     * @TarsStructProperty(order=2, required=true, type="vector<SimpleStruct>")
     *
     * @var SimpleStruct[]
     */
    public $structList;
    /**
     * @TarsStructProperty(order=3, required=true, type="map<string, SimpleStruct>")
     *
     * @var array
     */
    public $structMap;
    /**
     * @TarsStructProperty(order=9, required=true, type="map<string, vector<SimpleStruct>>")
     *
     * @var array
     */
    public $mapOfList;
}
