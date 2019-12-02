<?php


namespace wenbinye\tars\protocol;

require_once __DIR__ . '/SimpleStruct.php';
require_once __DIR__ . '/NestedStruct.php';

use PHPUnit\Framework\TestCase;

class SimpleStructTest extends TestCase
{
    public $iVersion = 3;
    public $iRequestId = 1;
    public $servantName = 'test.test.test';
    public $funcName = 'example';
    public $cPacketType = 0;
    public $iMessageType = 0;
    public $iTimeout = 2;
    public $contexts = array('test' => 'testYong');
    public $statuses = array('test' => 'testStatus');

    public function testSimpleStruct()
    {
        $simpleStruct = new \SimpleStruct();
        $simpleStruct->id = 1;
        $simpleStruct->count = 2;

        $buf = \TUPAPI::putStruct('struct', $simpleStruct);

        $encodeBufs['struct'] = $buf;

        $requestBuf = \TUPAPI::encode($this->iVersion, $this->iRequestId, $this->servantName,
            $this->funcName, $this->cPacketType, $this->iMessageType, $this->iTimeout,
            $this->contexts, $this->statuses, $encodeBufs);

        $decodeRet = \TUPAPI::decode($requestBuf);
        if ($decodeRet['status'] !== 0) {
        }
        $respBuf = $decodeRet['sBuffer'];

        $outSimpleStruct = new \SimpleStruct();
        $result = \TUPAPI::getStruct('struct', $outSimpleStruct, $respBuf);
        $this->fromArray($result, $outSimpleStruct);

        $this->assertEquals($simpleStruct, $outSimpleStruct);
    }

    public function testComplicateStruct()
    {
        $simpleStruct1 = new \SimpleStruct();
        $simpleStruct1->id = 1;
        $simpleStruct1->count = 2;

        $nestedStruct = new \NestedStruct();
        $nestedStruct->structMap->pushBack(['test1' => $simpleStruct1]);
        $nestedStruct->structMap->pushBack(['test4' => $simpleStruct1]);

        $nestedStruct->structList->pushBack($simpleStruct1);
        $nestedStruct->structList->pushBack($simpleStruct1);

        $nestedStruct->nestedStruct = $simpleStruct1;

        $structList = new \TARS_VECTOR(new \SimpleStruct());
        $structList->pushBack($simpleStruct1);

        $nestedStruct->ext->pushBack(['test2' => $structList]);
        $nestedStruct->ext->pushBack(['test3' => $structList]);

        $nestedStruct->vecstruct->pushBack($simpleStruct1);

        $nestedStruct->vecchar->pushBack('a');

        $buf = \TUPAPI::putStruct('struct', $nestedStruct);

        $encodeBufs['struct'] = $buf;

        $requestBuf = \TUPAPI::encode($this->iVersion, $this->iRequestId, $this->servantName,
            $this->funcName, $this->cPacketType, $this->iMessageType, $this->iTimeout,
            $this->contexts, $this->statuses, $encodeBufs);

        $decodeRet = \TUPAPI::decode($requestBuf);

        $respBuf = $decodeRet['sBuffer'];

        $nestedStructOut = new \NestedStruct();

        $result = \TUPAPI::getStruct('struct', $nestedStructOut, $respBuf);
        $this->fromArray($result, $nestedStructOut);

        $this->assertEquals($simpleStruct1, $nestedStructOut->nestedStruct);
    }

    public function fromArray($data, &$structObj)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (method_exists($structObj, 'set'.ucfirst($key))) {
                    call_user_func_array([$this, 'set'.ucfirst($key)], [$value]);
                } elseif ($structObj->$key instanceof \TARS_Struct) {
                    $this->fromArray($value, $structObj->$key);
                } else {
                    $structObj->$key = $value;
                }
            }
        }
    }
}