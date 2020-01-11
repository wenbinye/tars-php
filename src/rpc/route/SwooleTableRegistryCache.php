<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\SimpleCache\CacheInterface;
use Swoole\Table;
use wenbinye\tars\registry\EndpointF;

class SwooleTableRegistryCache implements CacheInterface
{
    public const KEY_ROUTES = 'routes';
    public const KEY_EXPIRES = 'expires';
    /**
     * @var Table
     */
    private $table;

    /**
     * @var int
     */
    private $ttl;

    /**
     * SwooleTableRegistryCache constructor.
     */
    public function __construct($ttl = 60, $size = 256)
    {
        //100个服务,每个长度1000 需要100000个字节,这里申请200行,对应200个服务
        $this->table = new Table($size);
        $this->table->column(self::KEY_ROUTES, \swoole_table::TYPE_STRING, 1000);
        $this->table->column(self::KEY_EXPIRES, \swoole_table::TYPE_INT, 4);
        $this->table->create();
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $result = $this->table->get($key);
        if ($result && time() < $result[self::KEY_EXPIRES]) {
            return \unserialize($result[self::KEY_ROUTES], ['allowed_classes' => [Route::class]]);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $data = \serialize($value);
        $this->table->set($key,
            [self::KEY_ROUTES => $data, self::KEY_EXPIRES => time() + ($ttl ?? $this->ttl)]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $this->table->del($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $keys = [];
        foreach ($this->table as $key => $row) {
            $keys[] = $key;
        }
        $this->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->table->del($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->table->exist($key);
    }
}
