<?php

namespace wenbinye\tars\protocol\fixtures;

class NestedStructOld extends \TARS_Struct
{
    const SIMPLESTRUCT = 1;
    const STRUCTLIST = 2;
    const STRUCTMAP = 3;
    const MAPOFLIST = 9;

    public $simpleStruct;
    public $structList;
    public $structMap;
    public $mapOfList;

    protected static $_fields = [
        self::SIMPLESTRUCT => [
            'name' => 'simpleStruct',
            'required' => true,
            'type' => \TARS::STRUCT,
        ],
        self::STRUCTLIST => [
            'name' => 'structList',
            'required' => true,
            'type' => \TARS::VECTOR,
        ],
        self::STRUCTMAP => [
            'name' => 'structMap',
            'required' => true,
            'type' => \TARS::MAP,
        ],
        self::MAPOFLIST => [
            'name' => 'mapOfList',
            'required' => true,
            'type' => \TARS::MAP,
        ],
    ];

    public function __construct()
    {
        parent::__construct('PHPTest_PHPServer_obj_NestedStruct', self::$_fields);
        $this->simpleStruct = new SimpleStructOld();
        $this->structList = new \TARS_Vector(new SimpleStructOld());
        $this->structMap = new \TARS_Map(\TARS::STRING, new SimpleStructOld());
        $this->mapOfList = new \TARS_Map(\TARS::STRING, new \TARS_Vector(new SimpleStructOld()));
    }
}
