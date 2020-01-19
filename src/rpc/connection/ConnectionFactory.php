<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\pool\PoolConfig;
use kuiper\swoole\pool\PoolInterface;
use kuiper\swoole\pool\SimplePool;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\route\RouteHolderFactoryInterface;

class ConnectionFactory implements ConnectionFactoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteHolderFactoryInterface
     */
    private $routeHolderFactory;

    /**
     * @var PoolConfig[]
     */
    private $poolConfig;

    /**
     * @var array
     */
    private $clientSettings;

    /**
     * @var PoolInterface[]
     */
    private $pools;

    /**
     * ConnectionFactory constructor.
     */
    public function __construct(RouteHolderFactoryInterface $routeHolderFactory)
    {
        $this->routeHolderFactory = $routeHolderFactory;
    }

    public function setPoolConfig(string $servantName, PoolConfig $poolConfig): void
    {
        $this->poolConfig[$servantName] = $poolConfig;
    }

    public function setClientSetting(string $servantName, array $setting): void
    {
        $this->clientSettings[$servantName] = $setting;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $servantName): ConnectionInterface
    {
        return new PoolConnection($this->getConnectionPool($servantName));
    }

    public function getConnectionPool(string $servantName): PoolInterface
    {
        if (!isset($this->pools[$servantName])) {
            $this->logger && $this->logger->debug('[ConnectionFactory] create pool', ['servant' => $servantName]);
            $this->pools[$servantName] = new SimplePool(function () use ($servantName) {
                $connectionClass = Coroutine::isEnabled() ? SwooleCoroutineTcpConnection::class : SwooleTcpConnection::class;
                $routeHolder = $this->routeHolderFactory->create($servantName);
                $conn = new $connectionClass($routeHolder);
                if (isset($this->clientSettings[$servantName])) {
                    $conn->setOptions($this->clientSettings[$servantName]);
                }
                if ($this->logger) {
                    $conn->setLogger($this->logger);
                }

                return $conn;
            }, $this->poolConfig[$servantName] ?? new PoolConfig());
        }

        return $this->pools[$servantName];
    }
}
