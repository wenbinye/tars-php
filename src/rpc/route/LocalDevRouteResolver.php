<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class LocalDevRouteResolver implements RouteResolverInterface
{
    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;
    /**
     * @var array
     */
    private $ipMapping;

    public function __construct(RouteResolverInterface $routeResolver, array $ipMapping = [])
    {
        $this->routeResolver = $routeResolver;
        $this->ipMapping = $ipMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $servantName): array
    {
        $routes = $this->routeResolver->resolve($servantName);
        foreach ($routes as $i => $route) {
            if (isset($this->ipMapping[$route->getHost()])) {
                $routes[$i] = $route->withHost($this->ipMapping[$route->getHost()]);
            }
        }

        return $routes;
    }
}
