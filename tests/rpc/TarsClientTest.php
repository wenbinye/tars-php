<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use wenbinye\tars\rpc\middleware\Retry;
use wenbinye\tars\rpc\route\RegistryRouteResolver;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\server\fixtures\HelloServant;

class TarsClientTest extends TestCase
{
    public function testReloadFromRegistry()
    {
        // todo 不仅要从注册中心重新获取配置，同时需要对某个节点标记不可用
        $testLogger = new TestLogger();
        $routeResolver = \Mockery::mock(RegistryRouteResolver::class);
        $routeResolver->shouldReceive('resolve')
            ->andReturnUsing(function ($args) {
                var_export(['resolve', $args]);

                return Route::fromString('PHPTest.PHPTcpServer.obj@tcp -h 10.1.1.204 -p 10204 -t 60000');
            });
        $routeResolver->shouldReceive('clear')
            ->with(\Mockery::on(function ($args) {
                var_export(['clear', $args]);

                return true;
            }));
        $retry = new Retry($routeResolver);
        $retry->setLogger($testLogger);
        $client = TarsClient::builder()
            ->setRouteResolver($routeResolver)
            ->addMiddleware($retry)
            ->createProxy(HelloServant::class);
        $client->hello('hello');
    }
}
