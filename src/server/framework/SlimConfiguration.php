<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use kuiper\di\annotation\Bean;
use kuiper\di\annotation\ConditionalOnProperty;
use kuiper\di\annotation\Configuration;
use kuiper\web\SlimAppFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\ServerProperties;

/**
 * @Configuration()
 * @ConditionalOnProperty("application.web.framework", hasValue="slim")
 */
class SlimConfiguration
{
    /**
     * @Bean
     */
    public function slimApp(ContainerInterface $container): App
    {
        return SlimAppFactory::create($container);
    }

    /**
     * @Bean()
     */
    public function requestHandler(
        App $app,
        ContainerInterface $container,
        ServerProperties $serverProperties): RequestHandlerInterface
    {
        $middlewares = Config::getInstance()->get('application.middleware.web', []);
        foreach ($middlewares as $middleware) {
            $app->addMiddleware($container->get($middleware));
        }
        $routeFile = $serverProperties->getBasePath().'/src/routes.php';
        if (file_exists($routeFile)) {
            require $routeFile;
        }

        return $app;
    }
}
