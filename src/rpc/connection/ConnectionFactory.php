<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\pool\PoolFactoryInterface;
use kuiper\swoole\pool\PoolInterface;
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
     * @var PoolFactoryInterface
     */
    private $poolFactory;
    /**
     * @var ServerAddressHolderFactoryInterface
     */
    private $serverAddressHolderFactory;
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
    public function __construct(
        PoolFactoryInterface $poolFactory,
        ServerAddressHolderFactoryInterface $serverAddressHolderFactory,
        ?LoggerInterface $logger)
    {
        $this->poolFactory = $poolFactory;
        $this->serverAddressHolderFactory = $serverAddressHolderFactory;
        $this->setLogger($logger ?? new NullLogger());
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
        if (!isset($this->pools[$servantName])) {
            $connectionFactory = function ($connId) use ($servantName) {
                $connectionClass = Coroutine::isEnabled() ? SwooleCoroutineTcpConnection::class : SwooleTcpConnection::class;
                $this->logger->info(static::TAG."create connection $servantName#$connId",
                    ['class' => $connectionClass]);
                $routeHolder = $this->serverAddressHolderFactory->create($servantName);
                /** @var ConnectionInterface $conn */
                $conn = new $connectionClass($routeHolder, $this->logger);
                if (isset($this->clientSettings[$servantName])) {
                    $conn->setOptions($this->clientSettings[$servantName]);
                }

                return $conn;
            };
            $this->pools[$servantName] = $this->poolFactory->create($servantName, $connectionFactory);
        }

        return $this->pools[$servantName]->take();
    }
}
