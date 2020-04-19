<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use kuiper\di\annotation\Bean;
use kuiper\di\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use wenbinye\tars\server\ServerProperties;

class Slim extends ContainerFactory
{
    protected function createContainerBuilder(): ContainerBuilder
    {
        return parent::createContainerBuilder()
            ->addConfiguration($this);
    }

    /**
     * @Bean()
     */
    public function requestHandler(ContainerInterface $container, ServerProperties $serverProperties): RequestHandlerInterface
    {
        $app = AppFactory::create(null, $container);
        $routeFile = $serverProperties->getBasePath().'/src/routes.php';
        if (file_exists($routeFile)) {
            require $routeFile;
        }

        return $app;
    }
}
