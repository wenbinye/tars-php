<?php

require __DIR__ . '/SimpleStruct.php';
// require __DIR__ . '/NestedStruct.php';

$simpleStruct1 = new \SimpleStruct();
$simpleStruct1->id = 1;
$simpleStruct1->count = 2;

class NestedStruct extends \TARS_Struct
{
    const STRUCTMAP = 1;
    public $structMap;
    
    protected static $fields = array(
        self::STRUCTMAP => array(
            'name' => 'structMap',
            'required' => true,
            'type' => \TARS::MAP,
            ),
    );
    
    public function __construct()
    {
        parent::__construct('App_Server_Servant.NestedStruct', self::$fields);
        
        $this->structMap = new \TARS_MAP(\TARS::STRING, new SimpleStruct());
    }
}


$nestedStruct = new \NestedStruct();
$nestedStruct->structMap->pushBack(['test1' => $simpleStruct1]);
$nestedStruct->structMap->pushBack(['test4' => $simpleStruct1]);

$buf = \TUPAPI::putStruct('struct', $nestedStruct);

$struct = new \TARS_Struct('mystruct', [
    1 => array(
        'name' => 'structMap',
        'required' => true,
        'type' => \TARS::MAP,
    ),
]);
$struct->structMap = new \TARS_MAP(\TARS::STRING, new SimpleStruct());
$struct->structMap->pushBack(['test1' => $simpleStruct1]);
$struct->structMap->pushBack(['test4' => $simpleStruct1]);
$buf2 = \TUPAPI::putStruct('struct', $struct);

var_export([
    $buf, $buf2, $buf == $buf2
]);

exit;
$nestedStructOut = new \NestedStruct();

$result = \TUPAPI::getStruct('struct', $nestedStructOut, $buf);

var_export($result);
