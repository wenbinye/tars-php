<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use wenbinye\tars\server\annotation\Bean;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\support\ContainerFactoryInterface;

class Slim implements ContainerFactoryInterface
{
    /**
     * @var PhpDiContainerFactory
     */
    private $phpDiFactory;

    /**
     * @var callable
     */
    private $routeFactory;

    public function __construct(array $config = [], callable $routeFactory = null)
    {
        $this->phpDiFactory = new PhpDiContainerFactory($config);
        $this->routeFactory = $routeFactory;
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
        if ($this->routeFactory) {
            call_user_func($this->routeFactory, $app);
        }

        return $app;
    }

    public function create(): ContainerInterface
    {
        $this->phpDiFactory->getBeanConfigurationSource()->addConfiguration($this);

        return $this->phpDiFactory->create();
    }
}
