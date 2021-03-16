<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use wenbinye\tars\rpc\connection\TestConnectionFactory;
use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\middleware\History;
use wenbinye\tars\rpc\middleware\Retry;
use wenbinye\tars\rpc\route\RegistryRouteResolver;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\server\fixtures\HelloServant;
use wenbinye\tars\server\fixtures\OutputParamServant;

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

    public function testExecutor()
    {
        $mock = new TestConnectionFactory();
        $client = TarsClient::builder()
            ->setConnectionFactory($mock)
            ->createProxy(HelloServant::class);

        $mock->pushResponse(['ok']);
        $executor = $client->createExecutor('hello');
        // print_r($executor);
        $ret = $executor->execute();
        $this->assertEquals($ret, 'ok');
    }

    public function testExecutorWithOutParam()
    {
        $mock = new TestConnectionFactory();
        $client = TarsClient::builder()
            ->setConnectionFactory($mock)
            ->createProxy(OutputParamServant::class);

        $mock->pushResponse(['it', 'ok']);
        $executor = $client->createExecutor('hello');
        // print_r($executor);
        $ret = $executor->execute();
        // var_export($ret);
        $this->assertEquals($ret, ['it', 'ok']);
    }

    public function testExecutorWithFilter()
    {
        $mock = new TestConnectionFactory();
        $history = new History();
        $client = TarsClient::builder()
            ->setConnectionFactory($mock)
            ->addMiddleware($history)
            ->createProxy(OutputParamServant::class);

        $mock->pushResponse(['it', 'ok']);
        /** @var RpcExecutor $executor */
        $executor = $client->createExecutor('hello');
        $executor->withRequestFilter(new class() implements RequestFilterInterface {
            public function filter(ClientRequestInterface $request): ClientRequestInterface
            {
                return $request->withStatus([
                    'jaeger-debug-id' => '1',
                ]);
            }
        });
        // print_r($executor);
        $ret = $executor->execute();
        // var_export($ret);
        $this->assertEquals($ret, ['it', 'ok']);
        $reqs = $history->getHistories();
        $this->assertEquals($reqs[0]['request']->getStatus(), [
            'jaeger-debug-id' => '1',
        ]);
    }
}
