<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\route\LoadBalanceRouteHolder;
use wenbinye\tars\rpc\route\RouteHolder;
use wenbinye\tars\rpc\route\RouteHolderInterface;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\support\loadBalance\LoadBalanceInterface;

// TODO use pool connection
class ConnectionFactory implements ConnectionFactoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;
    /**
     * @var string
     */
    private $loadBalance;

    /**
     * ConnectionFactory constructor.
     *
     * @param string $loadBalanceAlgorithm the LoadBalanceInterface concrete class
     */
    public function __construct(RouteResolverInterface $routeResolver, string $loadBalanceAlgorithm = null)
    {
        $this->routeResolver = $routeResolver;
        $this->loadBalance = $loadBalanceAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $servantName): ConnectionInterface
    {
        return new SocketTcpConnection($this->createRouteHolder($servantName));
    }

    private function createRouteHolder(string $servantName): RouteHolderInterface
    {
        if ($this->loadBalance) {
            $routeHolder = new LoadBalanceRouteHolder($this->routeResolver, $this->loadBalance, $servantName);
            $routeHolder->setLogger($this->logger);

            return $routeHolder;
        } else {
            $routes = $this->routeResolver->resolve($servantName);
            if (empty($routes)) {
                throw new \InvalidArgumentException("Cannot resolve route for $servantName");
            }

            return new RouteHolder($routes[0]);
        }
    }
}
