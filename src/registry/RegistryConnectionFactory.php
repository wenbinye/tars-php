<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheInterface;
use wenbinye\tars\registry\exception\RegistryException;
use wenbinye\tars\rpc\ConnectionFactoryInterface;
use wenbinye\tars\rpc\ConnectionInterface;
use wenbinye\tars\rpc\SocketTcpConnection;

class RegistryConnectionFactory implements ConnectionFactoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RegistryRouteResolver[]
     */
    private $routeResolvers;
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

    public function has(string $servantName): bool
    {
        try {
            $this->getRouteResolver($servantName)->resolve();

            return true;
        } catch (RegistryException $e) {
            return false;
        }
    }

    public function create(string $servantName): ConnectionInterface
    {
        return new SocketTcpConnection($this->getRouteResolver($servantName));
    }

    private function getRouteResolver(string $servantName): RegistryRouteResolver
    {
        if (!isset($this->routeResolvers[$servantName])) {
            $resolver = new RegistryRouteResolver($servantName, $this->queryFClient, $this->cache, $this->ttl);
            $resolver->setLogger($this->logger);
            $this->routeResolvers[$servantName] = $resolver;
        }

        return $this->routeResolvers[$servantName];
    }
}
