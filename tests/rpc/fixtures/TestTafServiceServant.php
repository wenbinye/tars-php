<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\fixtures;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(servant="PHPTest.PHPTcpServer.obj")
 */
interface TestTafServiceServant
{
    /**
     * @TarsReturnType(type = "void")
     *
     * @return void
     */
    public function testTafServer();

    /**
     * @TarsParameter(name = "tags", type = "LotofTags")
     * @TarsParameter(name = "outtags", type = "LotofTags", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param \wenbinye\tars\testtaf\LotofTags $tags
     * @param \wenbinye\tars\testtaf\LotofTags $outtags
     *
     * @return int
     */
    public function testLofofTags($tags, &$outtags);

    /**
     * @TarsParameter(name = "name", type = "string")
     * @TarsParameter(name = "outGreetings", type = "string", out=true)
     * @TarsReturnType(type = "void")
     *
     * @param string $name
     * @param string $outGreetings
     *
     * @return void
     */
    public function sayHelloWorld($name, &$outGreetings);

    /**
     * @TarsParameter(name = "a", type = "bool")
     * @TarsParameter(name = "b", type = "int")
     * @TarsParameter(name = "c", type = "string")
     * @TarsParameter(name = "d", type = "bool", out=true)
     * @TarsParameter(name = "e", type = "int", out=true)
     * @TarsParameter(name = "f", type = "string", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param bool $a
     * @param int $b
     * @param string $c
     * @param bool $d
     * @param int $e
     * @param string $f
     *
     * @return int
     */
    public function testBasic($a, $b, $c, &$d, &$e, &$f);

    /**
     * @TarsParameter(name = "a", type = "long")
     * @TarsParameter(name = "b", type = "SimpleStruct")
     * @TarsParameter(name = "d", type = "OutStruct", out=true)
     * @TarsReturnType(type = "string")
     *
     * @param int $a
     * @param \wenbinye\tars\testtaf\SimpleStruct $b
     * @param \wenbinye\tars\testtaf\OutStruct $d
     *
     * @return string
     */
    public function testStruct($a, $b, &$d);

    /**
     * @TarsParameter(name = "a", type = "short")
     * @TarsParameter(name = "b", type = "SimpleStruct")
     * @TarsParameter(name = "m1", type = "map<string, string>")
     * @TarsParameter(name = "d", type = "OutStruct", out=true)
     * @TarsParameter(name = "m2", type = "map<int, SimpleStruct>", out=true)
     * @TarsReturnType(type = "string")
     *
     * @param int $a
     * @param \wenbinye\tars\testtaf\SimpleStruct $b
     * @param array $m1
     * @param \wenbinye\tars\testtaf\OutStruct $d
     * @param array $m2
     *
     * @return string
     */
    public function testMap($a, $b, $m1, &$d, &$m2);

    /**
     * @TarsParameter(name = "a", type = "int")
     * @TarsParameter(name = "v1", type = "vector<string>")
     * @TarsParameter(name = "v2", type = "vector<SimpleStruct>")
     * @TarsParameter(name = "v3", type = "vector<int>", out=true)
     * @TarsParameter(name = "v4", type = "vector<OutStruct>", out=true)
     * @TarsReturnType(type = "string")
     *
     * @param int $a
     * @param array $v1
     * @param array $v2
     * @param array $v3
     * @param array $v4
     *
     * @return string
     */
    public function testVector($a, $v1, $v2, &$v3, &$v4);

    /**
     * @TarsReturnType(type = "SimpleStruct")
     *
     * @return \wenbinye\tars\testtaf\SimpleStruct
     */
    public function testReturn();

    /**
     * @TarsReturnType(type = "map<string, string>")
     *
     * @return array
     */
    public function testReturn2();

    /**
     * @TarsParameter(name = "cs", type = "ComplicatedStruct")
     * @TarsParameter(name = "vcs", type = "vector<ComplicatedStruct>")
     * @TarsParameter(name = "ocs", type = "ComplicatedStruct", out=true)
     * @TarsParameter(name = "ovcs", type = "vector<ComplicatedStruct>", out=true)
     * @TarsReturnType(type = "vector<SimpleStruct>")
     *
     * @param \wenbinye\tars\testtaf\ComplicatedStruct $cs
     * @param array $vcs
     * @param \wenbinye\tars\testtaf\ComplicatedStruct $ocs
     * @param array $ovcs
     *
     * @return array
     */
    public function testComplicatedStruct($cs, $vcs, &$ocs, &$ovcs);

    /**
     * @TarsParameter(name = "mcs", type = "map<string, ComplicatedStruct>")
     * @TarsParameter(name = "omcs", type = "map<string, ComplicatedStruct>", out=true)
     * @TarsReturnType(type = "map<string, ComplicatedStruct>")
     *
     * @param array $mcs
     * @param array $omcs
     *
     * @return array
     */
    public function testComplicatedMap($mcs, &$omcs);

    /**
     * @TarsParameter(name = "a", type = "short")
     * @TarsParameter(name = "b1", type = "bool", out=true)
     * @TarsParameter(name = "in2", type = "int", out=true)
     * @TarsParameter(name = "d", type = "OutStruct", out=true)
     * @TarsParameter(name = "v3", type = "vector<OutStruct>", out=true)
     * @TarsParameter(name = "v4", type = "vector<int>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param int $a
     * @param bool $b1
     * @param int $in2
     * @param \wenbinye\tars\testtaf\OutStruct $d
     * @param array $v3
     * @param array $v4
     *
     * @return int
     */
    public function testEmpty($a, &$b1, &$in2, &$d, &$v3, &$v4);

    /**
     * @TarsReturnType(type = "int")
     *
     * @return int
     */
    public function testSelf();

    /**
     * @TarsReturnType(type = "int")
     *
     * @return int
     */
    public function testProperty();
}
