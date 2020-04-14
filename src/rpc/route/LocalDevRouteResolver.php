<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class LocalDevRouteResolver implements RouteResolverInterface
{
    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;

    public function __construct(RouteResolverInterface $routeResolver)
    {
        $this->routeResolver = $routeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $servantName): array
    {
        $routes = $this->routeResolver->resolve($servantName);
        foreach ($routes as $i => $route) {
            $routes[$i] = $route->withHost('127.0.0.1');
        }

        return $routes;
    }
}
