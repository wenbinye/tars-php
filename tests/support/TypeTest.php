<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /**
     * @param string $value
     * @param bool   $expected
     * @dataProvider boolValues
     */
    public function testBoolFromString($value, $expected)
    {
        $this->assertEquals($expected, Type::fromString('bool', $value), "expected $value is $expected");
    }

    public function boolValues()
    {
        return [
            ['1', true],
            ['True', true],
            ['true', true],
            ['on', true],
            ['TRUE', true],
            ['ON', true],
            ['3', false],
            ['a', false],
            ['false', false],
            ['False', false],
            ['off', false],
        ];
    }
}
