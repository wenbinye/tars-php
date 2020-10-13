<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class ChainRouteResolver implements RouteResolverInterface
{
    /**
     * @var RouteResolverInterface[]
     */
    private $resolvers;

    /**
     * ChainRouteResolver constructor.
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $servantName): ?Route
    {
        foreach ($this->resolvers as $resolver) {
            $route = $resolver->resolve($servantName);
            if (null !== $route) {
                return $route;
            }
        }

        return null;
    }
}
