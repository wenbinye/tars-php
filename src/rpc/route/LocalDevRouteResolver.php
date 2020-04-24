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
    public function resolve(string $servantName): ?Route
    {
        $servantRoute = $this->routeResolver->resolve($servantName);
        if ($servantRoute) {
            $addresses = [];
            foreach ($servantRoute->getAddressList() as $i => $address) {
                if (isset($this->ipMapping[$address->getHost()])) {
                    $addresses[] = $address->withHost($this->ipMapping[$address->getHost()]);
                } else {
                    $addresses[] = $address;
                }
            }

            return new Route($servantName, $addresses);
        }

        return $servantRoute;
    }
}
