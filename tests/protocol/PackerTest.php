<?php

namespace wenbinye\tars\protocol;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\fixtures\NestedStruct;
use wenbinye\tars\protocol\fixtures\NestedStructOld;
use wenbinye\tars\protocol\fixtures\SimpleStruct;
use wenbinye\tars\protocol\fixtures\SimpleStructOld;

class PackerTest extends TestCase
{
    const VERSION = 3;
    const REQUEST_ID = 1;
    const SERVANT_NAME = 'test.test.test';
    const FUNC_NAME = 'example';
    const PACKET_TYPE = 0;
    const MESSAGE_TYPE = 0;
    const TIMEOUT = 2;

    const ARG_NAME = 'arg';

    /**
     * @var Packer
     */
    private $packer;

    /**
     * @var TypeParser
     */
    private $parser;

    public static function setUpBeforeClass(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
    }

    protected function setUp(): void
    {
        $this->parser = new TypeParser();
        $this->packer = new Packer(new TarsTypeFactory(new AnnotationReader()));
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws exception\SyntaxErrorException
     *
     * @dataProvider packData
     */
    public function testPack($type, $data, $expect)
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $result = $this->packer->pack(self::ARG_NAME, $data, $this->parser->parse($type, $namespace));
        $this->assertEquals($expect, $result);
    }

    /**
     * @dataProvider packData
     */
    public function testUnpack($type, $expect, $payload)
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $result = $this->packer->unpack(self::ARG_NAME, $this->createPayload($payload), $this->parser->parse($type, $namespace));
        $this->assertEquals($expect, $result);
    }

    public function packData()
    {
        return [
            ['bool', true, \TUPAPI::putBool(self::ARG_NAME, true)],
            ['vector<int>', [1, 2], $this->value(function () {
                $vector = new \TARS_Vector(\TARS::INT32);
                $vector->pushBack(1);
                $vector->pushBack(2);

                return \TUPAPI::putVector(self::ARG_NAME, $vector);
            })],
            ['map<int, string>', [1 => 'foo', 2 => 'bar'], $this->value(function () {
                $map = new \TARS_Map(\TARS::INT32, \TARS::STRING);
                $map->pushBack([1 => 'foo']);
                $map->pushBack([2 => 'bar']);

                return \TUPAPI::putMap(self::ARG_NAME, $map);
            })],
            ['SimpleStruct', $this->createSimpleStruct(), \TUPAPI::putStruct(self::ARG_NAME, $this->createSimpleStructOld())],
            ['NestedStruct', $this->value(function () {
                $nestedStruct = new NestedStruct();
                $simpleStruct = $this->createSimpleStruct();
                $nestedStruct->simpleStruct = $simpleStruct;
                $nestedStruct->structList = [$simpleStruct];
                $nestedStruct->structMap = ['test2' => $simpleStruct, 'test3' => $simpleStruct];
                $nestedStruct->mapOfList = [
                    'test1' => [$simpleStruct],
                ];

                return $nestedStruct;
            }), $this->value(function () {
                $simpleStruct = $this->createSimpleStructOld();

                $nestedStruct = new NestedStructOld();
                $nestedStruct->simpleStruct = $simpleStruct;
                $nestedStruct->structMap->pushBack(['test2' => $simpleStruct]);
                $nestedStruct->structMap->pushBack(['test3' => $simpleStruct]);
                $nestedStruct->structList->pushBack($simpleStruct);

                $structList = new \TARS_VECTOR(new SimpleStructOld());
                $structList->pushBack($simpleStruct);
                $nestedStruct->mapOfList->pushBack(['test1' => $structList]);

                return \TUPAPI::putStruct(self::ARG_NAME, $nestedStruct);
            })],
        ];
    }

    public function testName()
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $simpleStruct = $this->createSimpleStructOld();

        $nestedStruct = new NestedStructOld();
        $nestedStruct->simpleStruct = $simpleStruct;
        $nestedStruct->structMap->pushBack(['test2' => $simpleStruct]);
        $nestedStruct->structMap->pushBack(['test3' => $simpleStruct]);
        $nestedStruct->structList->pushBack($simpleStruct);

        $structList = new \TARS_VECTOR(new SimpleStructOld());
        $structList->pushBack($simpleStruct);
        $nestedStruct->mapOfList->pushBack(['test1' => $structList]);

        $payload = $this->createPayload(\TUPAPI::putStruct(self::ARG_NAME, $nestedStruct));
        $data = $this->packer->unpack(self::ARG_NAME, $payload, $this->parser->parse('NestedStruct', $namespace));
        // var_export($data);
        $this->assertInstanceOf(NestedStruct::class, $data);
    }

    public function value(callable $gen)
    {
        return $gen();
    }

    private function createSimpleStruct(): SimpleStruct
    {
        $obj = new SimpleStruct();
        $obj->id = 10;
        $obj->count = 3;

        return $obj;
    }

    private function createSimpleStructOld(): SimpleStructOld
    {
        $obj = new SimpleStructOld();
        $obj->id = 10;
        $obj->count = 3;

        return $obj;
    }

    protected function createPayload(string $payload): string
    {
        $requestBuf = \TUPAPI::encode(self::VERSION, self::REQUEST_ID, self::SERVANT_NAME,
            self::FUNC_NAME, self::PACKET_TYPE, self::MESSAGE_TYPE, self::TIMEOUT,
            [], [], [self::ARG_NAME => $payload]);
        $decodeRet = \TUPAPI::decode($requestBuf);

        return $decodeRet['sBuffer'];
    }
}
