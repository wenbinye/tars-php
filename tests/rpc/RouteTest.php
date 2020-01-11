<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use PHPUnit\Framework\TestCase;
use wenbinye\tars\rpc\route\Route;

class RouteTest extends TestCase
{
    /**
     * @dataProvider routes
     */
    public function testFromString($str, $expect)
    {
        $route = Route::fromString($str);
        $this->assertEquals($route->toArray(), $expect);
    }

    public function routes()
    {
        return [
            ['tars.tarsnode.ServerObj@tcp -h 172.29.0.3 -p 19386 -t 60000', [
                'protocol' => 'tcp',
                'host' => '172.29.0.3',
                'port' => 19386,
                'timeout' => 60000,
                'servantName' => 'tars.tarsnode.ServerObj',
                'weight' => 100,
            ]],
            ['tcp -h 127.0.0.1 -p 18080 -t 3000', [
                'protocol' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 18080,
                'timeout' => 3000,
                'weight' => 100,
            ]],
        ];
    }
}
