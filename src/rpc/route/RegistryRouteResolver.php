<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\SimpleCache\CacheInterface;
use wenbinye\tars\client\EndpointF;
use wenbinye\tars\client\QueryFServant;

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
    public function resolve(string $servantName): ?Route
    {
        $addresses = $this->cache->get($servantName);
        if (null === $addresses) {
            $endpoints = $this->queryFClient->findObjectById($servantName);
            $addresses = array_map(static function (EndpointF $endpoint): ServerAddress {
                return new ServerAddress($endpoint->istcp > 0 ? 'tcp' : 'udp',
                    $endpoint->host, $endpoint->port, $endpoint->timeout, $endpoint->weight ?? 100);
            }, $endpoints);

            $this->cache->set($servantName, $addresses, $this->ttl);
        }

        return new Route($servantName, $addresses);
    }

    public function clear(string $servantName): void
    {
        $this->cache->delete($servantName);
    }
}
