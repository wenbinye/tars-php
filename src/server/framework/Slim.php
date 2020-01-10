<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use kuiper\di\annotation\Bean;
use kuiper\di\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use wenbinye\tars\server\ContainerFactoryInterface;
use wenbinye\tars\server\ServerProperties;

class Slim implements ContainerFactoryInterface
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function __construct(ClassLoader $classLoader, array $namespaces = [])
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setClassLoader($classLoader);
        if (!empty($namespaces)) {
            $this->componentScan($namespaces);
        }
    }

    public function getContainerBuilder(): ContainerBuilder
    {
        return $this->containerBuilder;
    }

    /**
     * @Bean()
     */
    public function requestHandler(ContainerInterface $container, ServerProperties $serverProperties): RequestHandlerInterface
    {
        $app = AppFactory::create(null, $container);
        $routeFile = $serverProperties->getBasePath().'/routes.php';
        if (file_exists($routeFile)) {
            require $routeFile;
        }

        return $app;
    }

    public function componentScan(array $namespaces): self
    {
        $this->containerBuilder->componentScan($namespaces);

        return $this;
    }

    public function create(): ContainerInterface
    {
        $builder = $this->containerBuilder;
        $builder->addConfiguration(new ServerConfiguration())
            ->addConfiguration($this);

        return $builder->build();
    }
}
