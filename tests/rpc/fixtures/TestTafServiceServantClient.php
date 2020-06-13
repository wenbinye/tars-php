<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\fixtures;

class TestTafServiceServantClient implements TestTafServiceServant
{
    private $client = null;

    public function __construct(\wenbinye\tars\rpc\TarsClient $client)
    {
        $this->client = $client;
    }

    public function testTafServer()
    {
        $this->client->call($this, __FUNCTION__);
    }

    public function testLofofTags($tags, &$outtags)
    {
        list($outtags, $ret) = $this->client->call($this, __FUNCTION__, $tags);

        return $ret;
    }

    public function sayHelloWorld($name, &$outGreetings)
    {
        list($outGreetings) = $this->client->call($this, __FUNCTION__, $name);
    }

    public function testBasic($a, $b, $c, &$d, &$e, &$f)
    {
        list($d, $e, $f, $ret) = $this->client->call($this, __FUNCTION__, $a, $b, $c);

        return $ret;
    }

    public function testStruct($a, $b, &$d)
    {
        list($d, $ret) = $this->client->call($this, __FUNCTION__, $a, $b);

        return $ret;
    }

    public function testMap($a, $b, $m1, &$d, &$m2)
    {
        list($d, $m2, $ret) = $this->client->call($this, __FUNCTION__, $a, $b, $m1);

        return $ret;
    }

    public function testVector($a, $v1, $v2, &$v3, &$v4)
    {
        list($v3, $v4, $ret) = $this->client->call($this, __FUNCTION__, $a, $v1, $v2);

        return $ret;
    }

    public function testReturn()
    {
        list($ret) = $this->client->call($this, __FUNCTION__);

        return $ret;
    }

    public function testReturn2()
    {
        list($ret) = $this->client->call($this, __FUNCTION__);

        return $ret;
    }

    public function testComplicatedStruct($cs, $vcs, &$ocs, &$ovcs)
    {
        list($ocs, $ovcs, $ret) = $this->client->call($this, __FUNCTION__, $cs, $vcs);

        return $ret;
    }

    public function testComplicatedMap($mcs, &$omcs)
    {
        list($omcs, $ret) = $this->client->call($this, __FUNCTION__, $mcs);

        return $ret;
    }

    public function testEmpty($a, &$b1, &$in2, &$d, &$v3, &$v4)
    {
        list($b1, $in2, $d, $v3, $v4, $ret) = $this->client->call($this, __FUNCTION__, $a);

        return $ret;
    }

    public function testSelf()
    {
        list($ret) = $this->client->call($this, __FUNCTION__);

        return $ret;
    }

    public function testProperty()
    {
        list($ret) = $this->client->call($this, __FUNCTION__);

        return $ret;
    }
}
