<?php

namespace wenbinye\tars\protocol;

use PHPUnit\Framework\TestCase;

class TypeTokenizerTest extends TestCase
{
    /**
     * @dataProvider types
     */
    public function testName(string $type, $expect)
    {
        $typeTokenizer = new TypeTokenizer($type);
        $this->assertEquals($typeTokenizer->tokenize(), $expect);
    }

    public function types()
    {
        return [
            ['int', [[TypeTokenizer::T_PRIMITIVE, \TARS::INT32]]],
            ['vector < int >', [
                [TypeTokenizer::T_VECTOR, null],
                [TypeTokenizer::T_LEFT_BRAKET, null],
                [TypeTokenizer::T_PRIMITIVE, \TARS::INT32],
                [TypeTokenizer::T_RIGHT_BRAKET, null],
            ]],
            ['map < int, string >', [
                [TypeTokenizer::T_MAP, null],
                [TypeTokenizer::T_LEFT_BRAKET, null],
                [TypeTokenizer::T_PRIMITIVE, \TARS::INT32],
                [TypeTokenizer::T_COMMA, null],
                [TypeTokenizer::T_PRIMITIVE, \TARS::STRING],
                [TypeTokenizer::T_RIGHT_BRAKET, null],
            ]],
            ['vector < SimpleStruct >', [
                [TypeTokenizer::T_VECTOR, null],
                [TypeTokenizer::T_LEFT_BRAKET, null],
                [TypeTokenizer::T_STRUCT, 'SimpleStruct'],
                [TypeTokenizer::T_RIGHT_BRAKET, null],
            ]],
        ];
    }
}
