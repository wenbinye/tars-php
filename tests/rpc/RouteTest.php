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
        $this->assertEquals($expect['servantName'], $route->getServantName());
        $this->assertEquals($expect['addresses'], array_map(function ($item) {return $item->toArray(); }, $route->getAddressList()));
    }

    public function routes()
    {
        return [
            ['tars.tarsnode.ServerObj@tcp -h 172.29.0.3 -p 19386 -t 60000', [
                'servantName' => 'tars.tarsnode.ServerObj',
                'addresses' => [
                    [
                        'protocol' => 'tcp',
                        'host' => '172.29.0.3',
                        'port' => 19386,
                        'timeout' => 60000,
                        'weight' => 100,
                    ],
                ], ],
            ],
            ['tars.tarsnode.ServerObj@tcp -h 172.29.0.3 -p 19386 -t 60000:tcp -h 172.29.0.4 -p 19386 -t 60000', [
                'servantName' => 'tars.tarsnode.ServerObj',
                'addresses' => [
                    [
                        'protocol' => 'tcp',
                        'host' => '172.29.0.3',
                        'port' => 19386,
                        'timeout' => 60000,
                        'weight' => 100,
                    ],
                    [
                        'protocol' => 'tcp',
                        'host' => '172.29.0.4',
                        'port' => 19386,
                        'timeout' => 60000,
                        'weight' => 100,
                    ],
                ], ],
            ],
        ];
    }
}
