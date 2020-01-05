<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\di\ContainerBuilder;

class ServiceTest extends TestCase
{
    public function testAnnotation()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $containerBuilder = new ContainerBuilder();
        $reflectionClass = new \ReflectionClass(Foo::class);
        $reader = new AnnotationReader();
        /** @var Service $service */
        $service = $reader->getClassAnnotation($reflectionClass, Service::class);
        $service->setClass($reflectionClass);
        $service->setContainerBuilder($containerBuilder);
        $service->process();
        $container = $containerBuilder->build();
        $this->assertTrue($container->has(FooInterface::class));
        $foo = $container->get(FooInterface::class);
        $this->assertInstanceOf(Foo::class, $foo);
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
