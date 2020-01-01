<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\listener\RequestEventListener;
use Zend\Diactoros\ServerRequestFactory;

class SlimTest extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
    }

    public function testAware()
    {
        $container = (new Slim())->create();
        $requestEventListener = $container->get(RequestEventListener::class);
        $this->assertAttributeInstanceOf(LoggerInterface::class, 'logger', $requestEventListener);
    }

    public function testRequestHandle()
    {
        $container = (new Slim([], function ($app) {
            $app->get('/', function ($req, $resp, $args) {
                $resp->getBody()->write('hello');

                return $resp;
            });
        }))->create();
        $app = $container->get(RequestHandlerInterface::class);
        $response = $app->handle(ServerRequestFactory::fromGlobals());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
