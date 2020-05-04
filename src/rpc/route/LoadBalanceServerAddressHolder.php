<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\lb\Algorithm;
use wenbinye\tars\rpc\lb\LoadBalanceInterface;

/**
 * Class LoadBalanceServerAddressHolder.
 */
class LoadBalanceServerAddressHolder implements RefreshableServerAddressHolderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

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
    public function __construct(RouteResolverInterface $routeResolver, string $loadBalanceAlgorithm,
                                string $servantName, ?LoggerInterface $logger)
    {
        $this->routeResolver = $routeResolver;
        $this->servantName = $servantName;
        $this->loadBalanceAlgorithm = $loadBalanceAlgorithm;
        $this->setLogger($logger ?? new NullLogger());
        $this->checkAlgorithm();
    }

    public function get(): ServerAddress
    {
        if (!$this->currentAddress) {
            try {
                $route = $this->routeResolver->resolve($this->servantName);
            } catch (\Exception $e) {
                $this->logger->error(static::TAG."Resolve {$this->servantName} failed: ".get_class($e).': '.$e->getMessage());
                throw new InvalidArgumentException('Cannot resolve route for servant '.$this->servantName, 0, $e);
            }
            if (!$route) {
                throw new InvalidArgumentException('Cannot resolve route for servant '.$this->servantName);
            }
            $this->loadBalance = $this->createLoadBalance($route->getAddressList(), array_map(static function (ServerAddress $route) {
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

    private function createLoadBalance(array $addresses, array $weights): LoadBalanceInterface
    {
        $className = $this->getConcreteClass();

        return new $className($addresses, $weights);
    }

    private function checkAlgorithm(): void
    {
        $className = $this->getConcreteClass();
        if (!class_exists($className)) {
            throw new InvalidArgumentException('unknown load balance type '.$this->loadBalanceAlgorithm);
        }
        if (!is_a($className, LoadBalanceInterface::class, true)) {
            throw new InvalidArgumentException("Load balance type $className should implements ".LoadBalanceInterface::class);
        }
    }

    private function getConcreteClass(): string
    {
        if (Algorithm::hasValue($this->loadBalanceAlgorithm)) {
            $implementation = Algorithm::fromValue($this->loadBalanceAlgorithm)->implementation;
        } else {
            $implementation = $this->loadBalanceAlgorithm;
        }

        return $implementation;
    }
}
