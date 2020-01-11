<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\support\loadBalance\LoadBalanceInterface;

class LoadBalanceRouteHolder implements RefreshableRouteHolderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;
    /**
     * @var string
     */
    private $servantName;
    /**
     * @var Route
     */
    private $currentRoute;

    /**
     * @var LoadBalanceInterface
     */
    private $routes;
    /**
     * @var string
     */
    private $loadBalanceAlgorithm;

    /**
     * LoadBalanceRouteHolder constructor.
     */
    public function __construct(RouteResolverInterface $routeResolver, string $loadBalanceAlgorithm, string $servantName)
    {
        $this->routeResolver = $routeResolver;
        $this->servantName = $servantName;
        $this->loadBalanceAlgorithm = $loadBalanceAlgorithm;
    }

    public function get(): Route
    {
        if (!$this->currentRoute) {
            try {
                $routes = $this->routeResolver->resolve($this->servantName);
            } catch (\Exception $e) {
                $this->logger->error("[LoadBalanceRouteHolder] Resolve {$this->servantName} failed: ".get_class($e).': '.$e->getMessage());
                throw new \InvalidArgumentException('Cannot resolve route for servant '.$this->servantName, 0, $e);
            }
            if (empty($routes)) {
                throw new \InvalidArgumentException('Cannot resolve route for servant '.$this->servantName);
            }
            $lb = $this->loadBalanceAlgorithm;
            $this->routes = new $lb($routes, array_map(static function (Route $route) {
                return $route->getWeight();
            }, $routes));
            $this->currentRoute = $this->routes->select();
        }

        return $this->currentRoute;
    }

    public function refresh(bool $force = false): void
    {
        if ($force) {
            $this->currentRoute = null;
        } elseif (isset($this->routes)) {
            $this->currentRoute = $this->routes->select();
        }
    }
}
