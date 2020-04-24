<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class InMemoryRouteResolver implements RouteResolverInterface
{
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * ConnectionFactory constructor.
     *
     * @param Route[] $routes
     */
    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    public function addRoute(Route $route): void
    {
        $this->routes[$route->getServantName()] = $route;
    }

    public function resolve(string $servantName): ?Route
    {
        return $this->routes[$servantName] ?? null;
    }
}
