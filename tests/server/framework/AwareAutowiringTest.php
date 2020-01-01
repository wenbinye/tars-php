<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use DI\ContainerBuilder;
use DI\Definition\Source\ReflectionBasedAutowiring;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AwareAutowiringTest extends TestCase
{
    public function testAware()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(new AwareAutowiring(new ReflectionBasedAutowiring(), [
            AwareInjection::create(LoggerAwareInterface::class),
        ]));
        $builder->addDefinitions([
            LoggerInterface::class => new NullLogger(),
        ]);
        $foo = $builder->build()->get(Foo::class);
        $this->assertInstanceOf(LoggerInterface::class, $foo->getLogger());
    }
}

class Foo implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
