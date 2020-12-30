<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route\cache;

use BadMethodCallException;
use Psr\SimpleCache\CacheInterface;

class ChainCache implements CacheInterface
{
    /**
     * @var CacheInterface[]
     */
    private $cacheList;

    public function __construct(array $cacheList)
    {
        $this->cacheList = $cacheList;
    }

    public function get($key, $default = null)
    {
        foreach ($this->cacheList as $cache) {
            $value = $cache->get($key);
            if (isset($value)) {
                return $value;
            }
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        foreach ($this->cacheList as $cache) {
            $cache->set($key, $value, $ttl);
        }

        return true;
    }

    public function delete($key): bool
    {
        foreach ($this->cacheList as $cache) {
            $cache->delete($key);
        }

        return true;
    }

    public function clear(): bool
    {
        foreach ($this->cacheList as $cache) {
            $cache->clear();
        }

        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        throw new BadMethodCallException('not implement');
    }

    public function setMultiple($values, $ttl = null): bool
    {
        throw new BadMethodCallException('not implement');
    }

    public function deleteMultiple($keys): bool
    {
        throw new BadMethodCallException('not implement');
    }

    public function has($key): bool
    {
        foreach ($this->cacheList as $cache) {
            if ($cache->has($key)) {
                return true;
            }
        }

        return false;
    }
}
