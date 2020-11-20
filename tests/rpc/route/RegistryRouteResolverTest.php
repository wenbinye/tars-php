<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use PHPUnit\Framework\TestCase;
use wenbinye\tars\client\EndpointF;
use wenbinye\tars\client\QueryFServant;
use wenbinye\tars\rpc\route\cache\ArrayCache;
use wenbinye\tars\rpc\route\cache\ChainCache;
use wenbinye\tars\rpc\route\cache\SwooleTableRegistryCache;

class RegistryRouteResolverTest extends TestCase
{
    public function testResolve()
    {
        $endpoint = new EndpointF();
        $endpoint->host = '127.0.0.1';
        $endpoint->port = 2222;
        $endpoint->istcp = 1;
        $endpoint->timeout = 2000;
        $queryClient = \Mockery::mock(QueryFServant::class);
        $queryClient->shouldReceive('findObjectById')
            ->andReturn([
                $endpoint,
            ]);
        $resolver = new RegistryRouteResolver($queryClient, $this->getCache([]));
        $ret = $resolver->resolve('a');
        // var_export($ret);
        $this->assertCount(1, $ret->getAddressList());
    }

    private function getCache(array $options)
    {
        $ttl = $options['ttl'] ?? 60;
        $capacity = $options['capacity'] ?? 256;
        $registryCache = new SwooleTableRegistryCache($ttl, $capacity, $options['size'] ?? 2048);

        return new ChainCache([
            new ArrayCache($options['memory-ttl'] ?? 1, $capacity),
            $registryCache,
        ]);
    }
}
