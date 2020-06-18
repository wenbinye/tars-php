<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\pool\PoolConfig;
use kuiper\swoole\pool\SimplePool;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\route\ServerAddressHolderFactoryInterface;

class ConnectionFactory implements ConnectionFactoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var ServerAddressHolderFactoryInterface
     */
    private $serverAddressHolderFactory;

    /**
     * @var PoolConfig[]
     */
    private $poolConfig;

    /**
     * @var array
     */
    private $clientSettings;

    /**
     * @var PooledConnection[]
     */
    private $connections;

    /**
     * ConnectionFactory constructor.
     */
    public function __construct(ServerAddressHolderFactoryInterface $serverAddressHolderFactory, ?LoggerInterface $logger)
    {
        $this->serverAddressHolderFactory = $serverAddressHolderFactory;
        $this->setLogger($logger ?? new NullLogger());
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
        if (!isset($this->connections[$servantName])) {
            $connectionFactory = function ($connId) use ($servantName) {
                $connectionClass = Coroutine::isEnabled() ? SwooleCoroutineTcpConnection::class : SwooleTcpConnection::class;
                $this->logger->info(static::TAG."create connection $servantName#$connId", ['class' => $connectionClass]);
                $routeHolder = $this->serverAddressHolderFactory->create($servantName);
                /** @var ConnectionInterface $conn */
                $conn = new $connectionClass($routeHolder, $this->logger);
                if (isset($this->clientSettings[$servantName])) {
                    $conn->setOptions($this->clientSettings[$servantName]);
                }

                return $conn;
            };
            $poolConfig = $this->poolConfig[$servantName] ?? new PoolConfig();
            $pool = new SimplePool($connectionFactory, $poolConfig);
            $this->connections[$servantName] = new PooledConnection($pool);
        }

        return $this->connections[$servantName];
    }
}
