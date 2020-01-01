<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\di\annotation\Service;
use wenbinye\tars\server\Config;

class PhpDiContainerFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function testComponentScan()
    {
        $loader = require __DIR__.'/../../../vendor/autoload.php';
        $factory = new PhpDiContainerFactory($loader);
        $factory->componentScan([__NAMESPACE__]);
        $container = $factory->create();
        $this->assertTrue($container->has(FooInterface::class));
        $this->assertInstanceOf(Foo::class, $container->get(FooInterface::class));
    }
}

interface FooInterface
{
}

/**
 * @Service()
 * Class Foo
 */
class Foo implements FooInterface
{
}
