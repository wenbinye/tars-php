<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\lb;

use PHPUnit\Framework\TestCase;
use wenbinye\tars\rpc\lb\RoundRobin;

class RoundRobinTest extends TestCase
{
    public function testSelect()
    {
        $hosts = ['a', 'b', 'c'];
        $rr = new RoundRobin($hosts, [5, 3, 2]);
        $this->assertContains($rr->select(), $hosts);
    }
}
