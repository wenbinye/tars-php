<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use kuiper\di\annotation\Bean;
use kuiper\di\annotation\ConditionalOnProperty;
use kuiper\di\annotation\Configuration;
use kuiper\web\SlimAppFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wenbinye\tars\server\ServerProperties;

/**
 * @Configuration()
 * @ConditionalOnProperty("application.web.framework", hasValue="slim")
 */
class SlimConfiguration
{
    /**
     * @Bean()
     */
    public function requestHandler(
        ContainerInterface $container,
        ServerProperties $serverProperties): RequestHandlerInterface
    {
        $app = SlimAppFactory::create($container);
        $routeFile = $serverProperties->getBasePath().'/src/routes.php';
        if (file_exists($routeFile)) {
            require $routeFile;
        }

        return $app;
    }
}
