<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class ConnectionFactory implements ConnectionFactoryInterface
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

    public function has(string $servantName): bool
    {
        return isset($this->routes[$servantName]);
    }

    public function create(string $servantName): ConnectionInterface
    {
        return new SocketTcpConnection(new RouteHolder($this->getRoute($servantName)));
    }

    private function getRoute(string $servantName): Route
    {
        if (!isset($this->routes[$servantName])) {
            throw new \InvalidArgumentException("unknown servant '$servantName'");
        }

        return $this->routes[$servantName];
    }
}
