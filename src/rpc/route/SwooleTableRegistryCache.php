<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Psr\SimpleCache\CacheInterface;
use Swoole\Table;

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
     *
     * @param int $ttl
     * @param int $size
     */
    public function __construct(int $ttl = 60, int $size = 256)
    {
        //100个服务,每个长度1000 需要100000个字节,这里申请200行,对应200个服务
        $this->table = new Table($size);
        $this->table->column(self::KEY_ROUTES, Table::TYPE_STRING, 1000);
        $this->table->column(self::KEY_EXPIRES, Table::TYPE_INT, 4);
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
            return \unserialize($result[self::KEY_ROUTES], ['allowed_classes' => [ServerAddress::class]]);
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

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $this->table->del($key);

        return true;
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

        return true;
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

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->table->del($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->table->exist($key);
    }
}
