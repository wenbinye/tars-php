<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\fixtures\EnumStruct;
use wenbinye\tars\protocol\fixtures\EnumStructOld;
use wenbinye\tars\protocol\fixtures\GoodType;
use wenbinye\tars\protocol\fixtures\NestedStruct;
use wenbinye\tars\protocol\fixtures\NestedStructOld;
use wenbinye\tars\protocol\fixtures\SimpleStruct;
use wenbinye\tars\protocol\fixtures\SimpleStructOld;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\protocol\type\StructMapEntry;

class PackerTest extends TestCase
{
    const VERSION = Version::TUP;
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

    protected function setUp(): void
    {
        $this->packer = new Packer(AnnotationReader::getInstance());
    }

    /**
     * @throws exception\SyntaxErrorException
     *
     * @dataProvider packData
     */
    public function testPack($type, $data, $expect): void
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $result = $this->packer->pack($this->packer->parse($type, $namespace), self::ARG_NAME, $data, self::VERSION);
        $this->assertEquals($expect, $result);
    }

    /**
     * @dataProvider packData
     *
     * @throws exception\SyntaxErrorException
     */
    public function testUnpack($type, $expect, $payload): void
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $buffer = Packer::toPayload(self::ARG_NAME, $payload);
        $result = $this->packer->unpack($this->packer->parse($type, $namespace), self::ARG_NAME, $buffer, self::VERSION);
        $this->assertEquals($expect, $result);
    }

    public function testUnpackStruct(): void
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $struct = new \TARS_Struct('StringStruct', [[
            'name' => 'name',
            'required' => true,
            'type' => \TARS::STRING,
        ]]);
        $struct->name = null;
        $buffer = Packer::toPayload(self::ARG_NAME, \TUPAPI::putStruct(self::ARG_NAME, $struct));
        $type = $this->packer->parse('StringStruct', $namespace);
        $result = $this->packer->unpack($type, self::ARG_NAME, $buffer, self::VERSION);
        $packer = new Packer(AnnotationReader::getInstance(), false);
        $hasEmptyString = $packer->unpack($type, self::ARG_NAME, $buffer, self::VERSION);
        // var_export([$result, $hasEmptyString]);
        $this->assertNull($result->name);
        $this->assertSame('', $hasEmptyString->name);
    }

    public function packData(): array
    {
        return [
            ['EnumStruct', $this->value(function () {
                $struct = new EnumStruct();
                $struct->type = GoodType::CITY();

                return $struct;
            }), $this->value(function () {
                $struct = new EnumStructOld();
                $struct->type = GoodType::CITY;

                return \TUPAPI::putStruct(self::ARG_NAME, $struct);
            })],
            ['SimpleStruct', $this->createSimpleStruct(), \TUPAPI::putStruct(self::ARG_NAME, $this->createSimpleStructOld())],
            ['GoodType', GoodType::CITY(), \TUPAPI::putUInt8(self::ARG_NAME, GoodType::CITY)],
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

    public function testMapOfStructKey()
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $simpleStruct = $this->createSimpleStructOld();

        $nestedStruct = new NestedStructOld();
        $nestedStruct->simpleStruct = $simpleStruct;
        $nestedStruct->structMap->pushBack(['test2' => $simpleStruct]);
        $nestedStruct->structMap->pushBack(['test3' => $simpleStruct]);
        $nestedStruct->structList->pushBack($simpleStruct);

        $structList = new \TARS_Map(new SimpleStructOld(), new NestedStructOld(), true);
        $structList->pushBack(['key' => $simpleStruct, 'value' => $nestedStruct]);

        $payload = Packer::toPayload(self::ARG_NAME, \TUPAPI::putMap(self::ARG_NAME, $structList));
        // var_export($payload);
        $data = $this->packer->unpack($this->packer->parse('map<SimpleStruct, NestedStruct>', $namespace), self::ARG_NAME, $payload, self::VERSION);
        // var_export($data);
        $this->assertInstanceOf(StructMap::class, $data);
        $this->assertEquals(1, $data->count());
        foreach ($data as $entry) {
            /* @var StructMapEntry $entry */
            $this->assertInstanceOf(SimpleStruct::class, $entry->getKey());
            $this->assertInstanceOf(NestedStruct::class, $entry->getValue());
            $this->assertEquals($entry->getKey()->id, 10);
            $this->assertEquals($entry->getValue()->simpleStruct->id, 10);
        }
        // $this->assertInstanceOf(NestedStruct::class, $data);
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
}
