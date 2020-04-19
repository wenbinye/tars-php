<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use function DI\decorate;
use kuiper\di\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use wenbinye\tars\rpc\route\LocalDevRouteResolver;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\framework\ServerConfiguration;

abstract class FunctionalTestCase extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
    }

    public function getContainer(): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addConfiguration(new ServerConfiguration());
        $containerBuilder->addDefinitions([
            RouteResolverInterface::class => decorate(function ($resolver) {
                return new LocalDevRouteResolver($resolver);
            }),
        ]);

        return $containerBuilder->build();
    }
}
