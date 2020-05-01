<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use kuiper\di\ContainerBuilder;
use Psr\Container\ContainerInterface;
use wenbinye\tars\server\ContainerFactoryInterface;

class ContainerFactory implements ContainerFactoryInterface
{
    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var string[]
     */
    private $namespaces;

    public function __construct(ClassLoader $classLoader, array $namespaces = [])
    {
        $this->classLoader = $classLoader;
        $this->namespaces = $namespaces;
    }

    protected function createContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setClassLoader($this->classLoader);
        if (!empty($this->namespaces)) {
            $containerBuilder->componentScan($this->namespaces);
        }
        $containerBuilder->addConfiguration(new FoundationConfiguration());
        $containerBuilder->addConfiguration(new ClientConfiguration());
        $containerBuilder->addConfiguration(new ServerConfiguration());
        $containerBuilder->addConfiguration(new DiactorosHttpMessageFactoryConfiguration());
        $containerBuilder->addConfiguration(new SlimConfiguration());

        return $containerBuilder;
    }

    public function create(): ContainerInterface
    {
        return $this->createContainerBuilder()->build();
    }
}
