<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use wenbinye\tars\di\annotation\Bean;
use wenbinye\tars\di\ContainerFactoryInterface;
use wenbinye\tars\server\ServerProperties;

class Slim implements ContainerFactoryInterface
{
    /**
     * @var PhpDiContainerFactory
     */
    private $phpDiContainerFactory;

    public function __construct(?ClassLoader $classLoader, array $namespaces = [])
    {
        $this->phpDiContainerFactory = new PhpDiContainerFactory($classLoader);
        if (!empty($namespaces)) {
            $this->componentScan($namespaces);
        }
    }

    public function getPhpDiContainerFactory(): PhpDiContainerFactory
    {
        return $this->phpDiContainerFactory;
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
        $this->phpDiContainerFactory->componentScan($namespaces);

        return $this;
    }

    public function create(): ContainerInterface
    {
        $this->phpDiContainerFactory->getBeanConfigurationSource()
            ->addConfiguration($this);

        return $this->phpDiContainerFactory->create();
    }
}
