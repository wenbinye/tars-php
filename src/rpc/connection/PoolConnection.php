<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\pool\PoolInterface;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\ServerAddress;

class PoolConnection implements ConnectionInterface
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * AutoReleaseConnection constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(PoolInterface $pool)
    {
        $this->pool = $pool;
    }

    private function getConnection(): ConnectionInterface
    {
        if (!$this->connection) {
            $this->connection = $this->pool->take();
        }

        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): void
    {
        $this->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->pool->release($this->connection);
            unset($this->connection);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected(): bool
    {
        return isset($this->connection);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress(): ServerAddress
    {
        return $this->getConnection()->getAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request): string
    {
        return $this->getConnection()->send($request);
    }

    public function setOptions(array $options): void
    {
    }
}
