<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ServerAddressHolderFactory implements ServerAddressHolderFactoryInterface, LoggerAwareInterface
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
    public function __construct(RouteResolverInterface $routeResolver, string $loadBalanceAlgorithm = null, LoggerInterface $logger = null)
    {
        $this->routeResolver = $routeResolver;
        $this->loadBalance = $loadBalanceAlgorithm;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $servantName): ServerAddressHolderInterface
    {
        if ($this->loadBalance) {
            return new LoadBalanceServerAddressHolder($this->routeResolver, $this->loadBalance, $servantName, $this->logger);
        }

        $servantRoute = $this->routeResolver->resolve($servantName);
        if (!$servantRoute) {
            throw new \InvalidArgumentException("Cannot resolve route for $servantName");
        }

        return new ServerAddressHolder($servantRoute);
    }
}
