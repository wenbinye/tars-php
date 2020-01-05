<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\Annotation\Inject;
use Monolog\Test\TestCase;
use wenbinye\tars\di\annotation\Bean;

class ConfigurationTest extends TestCase
{
    public function testBeanConfiguration()
    {
        $builder = new ContainerBuilder();
        $builder->addConfiguration(new BeanProvider());
        $container = $builder->build();
        $bar = $container->get(Bar::class);
        // var_export($bar);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertEquals('bar', $bar->name);
    }

    public function testInject()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        $builder->addConfiguration(new BeanProvider());
        $container = $builder->build();
        $bar = $container->get('foo');
        // var_export($bar);
        $this->assertInstanceOf(Bar::class, $bar);
        $this->assertEquals('other', $bar->name);
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

    /**
     * @Bean(name="otherBar")
     */
    public function otherBar(): Bar
    {
        return new Bar('other');
    }

    /**
     * @Bean("foo")
     * @Inject({"bar"="otherBar"})
     */
    public function foo(Bar $bar): Bar
    {
        return $bar;
    }
}
