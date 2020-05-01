<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use kuiper\swoole\listener\HttpRequestEventListener;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use wenbinye\tars\server\Config;
use Zend\Diactoros\ServerRequestFactory;

class SlimTest extends TestCase
{
    /**
     * @var ClassLoader
     */
    private $loader;

    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        $this->loader = require __DIR__.'/../../../vendor/autoload.php';
    }

    public function testAware()
    {
        $container = (new SlimConfiguration($this->loader))->create();
        $requestEventListener = $container->get(HttpRequestEventListener::class);
        $property = new \ReflectionProperty($requestEventListener, 'logger');
        $property->setAccessible(true);
        $this->assertInstanceOf(LoggerInterface::class, $property->getValue($requestEventListener));
        // $this->assertAttributeInstanceOf(LoggerInterface::class, 'logger', $requestEventListener);
    }

    public function testRequestHandle()
    {
        $container = (new SlimConfiguration($this->loader))->create();
        $app = $container->get(RequestHandlerInterface::class);
        $app->get('/', function ($req, $resp) { return $resp; });
        $response = $app->handle(ServerRequestFactory::fromGlobals());
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
