<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\lb;

use PHPUnit\Framework\TestCase;

class RoundRobinTest extends TestCase
{
    public function testSelect()
    {
        $hosts = ['a', 'b', 'c'];
        $rr = new RoundRobin($hosts, [5, 3, 2]);
        $this->assertContains($rr->select(), $hosts);
        $ret = array_map(function () use ($rr) {
            return $rr->select();
        }, range(1, 5));
        var_export($ret);
    }

    public function testNegativeWeight()
    {
        $hosts = ['a', 'b', 'c'];
        $rr = new RoundRobin($hosts, [0, 1, 1]);
        $ret = array_map(function () use ($rr) {
            return $rr->select();
        }, range(1, 5));
        var_export($ret);
    }
}
