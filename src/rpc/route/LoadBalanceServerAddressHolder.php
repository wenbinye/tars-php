<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\lb\LoadBalanceInterface;

class LoadBalanceServerAddressHolder implements RefreshableServerAddressHolderInterface, LoggerAwareInterface
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
     * @var ServerAddress
     */
    private $currentAddress;

    /**
     * @var LoadBalanceInterface
     */
    private $loadBalance;
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

    public function get(): ServerAddress
    {
        if (!$this->currentAddress) {
            try {
                $route = $this->routeResolver->resolve($this->servantName);
            } catch (\Exception $e) {
                $this->logger->error("[LoadBalanceRouteHolder] Resolve {$this->servantName} failed: ".get_class($e).': '.$e->getMessage());
                throw new \InvalidArgumentException('Cannot resolve route for servant '.$this->servantName, 0, $e);
            }
            if (!$route) {
                throw new \InvalidArgumentException('Cannot resolve route for servant '.$this->servantName);
            }
            $lb = $this->loadBalanceAlgorithm;
            $this->loadBalance = new $lb($route->getAddressList(), array_map(static function (ServerAddress $route) {
                return $route->getWeight();
            }, $route->getAddressList()));
            $this->currentAddress = $this->loadBalance->select();
        }

        return $this->currentAddress;
    }

    public function refresh(bool $force = false): void
    {
        if ($force) {
            $this->currentAddress = null;
        } elseif (isset($this->loadBalance)) {
            $this->currentAddress = $this->loadBalance->select();
        }
    }
}
