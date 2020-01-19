<?php


namespace wenbinye\tars\rpc\route;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class RouteHolderFactory implements RouteHolderFactoryInterface, LoggerAwareInterface
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
    public function create(string $servantName): RouteHolderInterface
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