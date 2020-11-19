<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\CommunicationException;
use wenbinye\tars\rpc\exception\ConnectFailedException;
use wenbinye\tars\rpc\exception\ConnectionClosedException;
use wenbinye\tars\rpc\exception\ConnectionException;
use wenbinye\tars\rpc\exception\ResolveAddressFailedException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\RefreshableServerAddressHolderInterface;
use wenbinye\tars\rpc\route\ServerAddress;
use wenbinye\tars\rpc\route\ServerAddressHolderInterface;

abstract class AbstractConnection implements ConnectionInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    private const ERROR_EXCEPTIONS = [
        ErrorCode::TARS_SOCKET_CLOSED => ConnectionClosedException::class,
        ErrorCode::TARS_SOCKET_CONNECT_FAILED => ConnectFailedException::class,
        ErrorCode::TARS_SOCKET_RECEIVE_FAILED => ConnectFailedException::class,
    ];

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
        if (isset($this->resource)) {
            $this->destroyResource();
            $this->resource = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect(): void
    {
        $this->disconnect();
    }

    /**
     * @return mixed
     */
    protected function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress(): ServerAddress
    {
        try {
            return $this->serverAddressHolder->get();
        } catch (\Exception $e) {
            throw new ResolveAddressFailedException($this, $e->getMessage(), ErrorCode::ROUTE_FAIL, $e);
        }
    }

    public function getServerAddressHolder(): ServerAddressHolderInterface
    {
        return $this->serverAddressHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request): string
    {
        $this->refreshAddress();
        $this->connect();
        $this->beforeSend();
        try {
            return $this->doSend($request);
        } finally {
            $this->afterSend();
        }
    }

    protected function refreshAddress(): void
    {
        if ($this->serverAddressHolder instanceof RefreshableServerAddressHolderInterface) {
            $this->serverAddressHolder->refresh();
        }
    }

    /**
     * Helper method to handle connection errors.
     *
     * @throws CommunicationException
     */
    protected function onConnectionError(ErrorCode $errorCode, string $message = null): void
    {
        $exception = $this->createException($errorCode, $message);
        $this->disconnect();
        $this->refreshAddress();

        CommunicationException::throwError($exception);
    }

    /**
     * @param ErrorCode   $errorCode
     * @param string|null $message
     *
     * @return mixed|ConnectionException
     */
    protected function createException(ErrorCode $errorCode, ?string $message)
    {
        $message = ($message ?? $errorCode->message).'(address='.$this->getAddress().')';
        if (array_key_exists($errorCode->value(), self::ERROR_EXCEPTIONS)) {
            $class = self::ERROR_EXCEPTIONS[$errorCode->value()];
            $exception = new $class($this, $message, $errorCode->value());
        } else {
            $exception = new ConnectionException($this, $message, $errorCode->value());
        }

        return $exception;
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
     * @throws CommunicationException
     */
    abstract protected function doSend(RequestInterface $request): string;

    /**
     * callback before send data.
     */
    protected function beforeSend(): void
    {
    }

    /**
     * callback after send data.
     */
    protected function afterSend(): void
    {
    }
}
