<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

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
        $builder->addAwareInjection(AwareInjection::create(LoggerAwareInterface::class));
        $builder->addDefinitions([
            LoggerInterface::class => new NullLogger(),
        ]);
        $container = $builder->build();
        $foo = $container->get(Foo::class);
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
