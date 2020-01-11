<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\SimpleCache\CacheInterface;
use wenbinye\tars\registry\EndpointF;
use wenbinye\tars\registry\QueryFServant;

class RegistryRouteResolver implements RouteResolverInterface
{
    /**
     * @var QueryFServant
     */
    private $queryFClient;

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var int
     */
    private $ttl;

    /**
     * RegistryConnectionFactory constructor.
     */
    public function __construct(QueryFServant $queryFClient, CacheInterface $cache, int $ttl = 60)
    {
        $this->queryFClient = $queryFClient;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $servantName): array
    {
        $routes = $this->cache->get($servantName);
        if (null !== $routes) {
            return $routes;
        }
        $endpoints = $this->queryFClient->findObjectById($servantName);
        $routes = array_map(static function (EndpointF $endpoint) use ($servantName) {
            return new Route($servantName, $endpoint->istcp ? 'tcp' : 'udp',
                $endpoint->host, $endpoint->port, $endpoint->timeout, $endpoint->weight ?? 100);
        }, $endpoints);

        $this->cache->set($servantName, $routes, $this->ttl);

        return $routes;
    }
}
