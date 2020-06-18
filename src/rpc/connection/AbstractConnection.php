<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\CommunicationException;
use wenbinye\tars\rpc\exception\ConnectionException;
use wenbinye\tars\rpc\exception\RetryableException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\RefreshableServerAddressHolderInterface;
use wenbinye\tars\rpc\route\ServerAddress;
use wenbinye\tars\rpc\route\ServerAddressHolderInterface;

abstract class AbstractConnection implements ConnectionInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const RETRYABLE_ERRORS = [ErrorCode::TARS_SOCKET_CLOSED];

    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var ServerAddressHolderInterface
     */
    private $serverAddressHolder;

    /**
     * AbstractConnection constructor.
     */
    public function __construct(ServerAddressHolderInterface $serverAddressHolder, ?LoggerInterface $logger)
    {
        $this->serverAddressHolder = $serverAddressHolder;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * Disconnects from the server and destroys the underlying resource when
     * PHP's garbage collector kicks in.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Creates the underlying resource used to communicate with server.
     *
     * @return mixed
     *
     * @throws CommunicationException
     */
    abstract protected function createResource();

    /**
     * Destroy the underlying resource.
     */
    abstract protected function destroyResource(): void;

    /**
     * {@inheritdoc}
     */
    public function isConnected(): bool
    {
        return isset($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): void
    {
        if (!$this->isConnected()) {
            $this->resource = $this->createResource();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        unset($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect(): void
    {
        $this->disconnect();
    }

    public function getResource()
    {
        if (isset($this->resource)) {
            return $this->resource;
        }

        $this->connect();

        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress(): ServerAddress
    {
        return $this->serverAddressHolder->get();
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request): string
    {
        $this->beforeSend();
        try {
            return $this->doSend($request);
        } finally {
            $this->afterSend();
        }
    }

    /**
     * @throws CommunicationException
     */
    abstract protected function doSend(RequestInterface $request): string;

    /**
     * Helper method to handle connection errors.
     *
     * @throws CommunicationException
     */
    protected function onConnectionError(ErrorCode $errorCode, string $message = null): void
    {
        $message = static::createExceptionMessage($this, $message ?? $errorCode->message);
        if (in_array($errorCode->value, self::RETRYABLE_ERRORS, true)) {
            $exception = new RetryableException($this, $message, $errorCode->value);
        } else {
            $exception = new ConnectionException($this, $message, $errorCode->value);
        }

        CommunicationException::handle($exception);
    }

    protected static function createExceptionMessage(ConnectionInterface $connection, string $message): string
    {
        // TODO: message format with request info
        return $message.'(address='.$connection->getAddress().')';
    }

    protected function beforeSend(): void
    {
        if ($this->serverAddressHolder instanceof RefreshableServerAddressHolderInterface) {
            $this->serverAddressHolder->refresh();
        }
    }

    protected function afterSend(): void
    {
    }
}
