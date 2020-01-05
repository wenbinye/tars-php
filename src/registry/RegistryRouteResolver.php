<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheInterface;
use wenbinye\tars\registry\exception\RegistryException;
use wenbinye\tars\rpc\RefreshableRouteResolverInterface;
use wenbinye\tars\rpc\Route;

class RegistryRouteResolver implements RefreshableRouteResolverInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @var string
     */
    private $servantName;

    /**
     * @var Route
     */
    private $currentRoute;

    /**
     * @var Route[]
     */
    private $routes;

    /**
     * RegistryConnectionFactory constructor.
     */
    public function __construct(string $servantName, QueryFServant $queryFClient, CacheInterface $cache, int $ttl)
    {
        $this->queryFClient = $queryFClient;
        $this->cache = $cache;
        $this->servantName = $servantName;
        $this->ttl = $ttl;
    }

    public function refresh(bool $force = false): void
    {
        if ($force) {
            $this->cache->delete($this->servantName);
            unset($this->currentRoute);
        }
    }

    public function resolve(): Route
    {
        if (!$this->currentRoute) {
            try {
                $this->routes = $this->getRoutes($this->servantName);
            } catch (\Exception $e) {
                $this->logger->error("Resolve {$this->servantName} failed: ".get_class($e).': '.$e->getMessage());
            }
            if (empty($this->routes)) {
                throw new RegistryException("Cannot resolve {$this->servantName}");
            }
            $this->setCurrentRoute($this->routes[random_int(0, count($this->routes) - 1)]);
        }

        return $this->currentRoute;
    }

    private function getRoutes(string $servantName): ?array
    {
        $endpoints = $this->cache->get($servantName);
        if (null !== $endpoints) {
            return null;
        }
        $endpoints = $this->queryFClient->findObjectById($servantName);
        $this->cache->set($servantName, $endpoints, $this->ttl);

        return array_map(static function (EndpointF $endpoint) use ($servantName) {
            return new Route($servantName, $endpoint->istcp ? 'tcp' : 'udp',
                $endpoint->host, $endpoint->port, $endpoint->timeout);
        }, $endpoints);
    }

    private function setCurrentRoute(Route $route)
    {
        $this->currentRoute = $route;
    }
}
