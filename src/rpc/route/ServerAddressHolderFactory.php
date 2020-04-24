<?php


namespace wenbinye\tars\rpc\route;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

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
    public function __construct(RouteResolverInterface $routeResolver, string $loadBalanceAlgorithm = null)
    {
        $this->routeResolver = $routeResolver;
        $this->loadBalance = $loadBalanceAlgorithm;
    }

    /**
     * @inheritDoc
     */
    public function create(string $servantName): ServerAddressHolderInterface
    {
        if ($this->loadBalance) {
            $loadBalanceServerAddressHolder = new LoadBalanceServerAddressHolder($this->routeResolver, $this->loadBalance, $servantName);
            $loadBalanceServerAddressHolder->setLogger($this->logger);

            return $loadBalanceServerAddressHolder;
        }

        $servantRoute = $this->routeResolver->resolve($servantName);
        if (!$servantRoute) {
            throw new \InvalidArgumentException("Cannot resolve route for $servantName");
        }

        return new ServerAddressHolder($servantRoute);
    }
}