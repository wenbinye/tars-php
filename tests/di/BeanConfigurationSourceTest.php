<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\ContainerBuilder;
use Monolog\Test\TestCase;
use wenbinye\tars\di\annotation\Bean;

class BeanConfigurationSourceTest extends TestCase
{
    public function testBeanConfiguration()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(new BeanConfigurationSource([new BeanProvider()]));
        $container = $builder->build();
        $bar = $container->get(Bar::class);
        // var_export($bar);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertEquals('bar', $bar->name);
    }
}

class Bar
{
    public $name;

    /**
     * Bar constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}

class BeanProvider
{
    /**
     * @Bean()
     */
    public function bar(): Bar
    {
        return new Bar('bar');
    }
}
