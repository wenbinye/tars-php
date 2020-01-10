<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use wenbinye\tars\rpc\connection\ConnectionInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\CommunicationException;
use wenbinye\tars\rpc\exception\ConnectionException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\RefreshableRouteResolverInterface;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\RouteResolverInterface;

abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var RouteResolverInterface
     */
    private $routeResolver;

    /**
     * AbstractConnection constructor.
     */
    public function __construct(RouteResolverInterface $routeResolver)
    {
        $this->routeResolver = $routeResolver;
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
     */
    abstract protected function createResource();

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
    public function getRoute(): Route
    {
        return $this->routeResolver->resolve();
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
     * @param ErrorCode
     *
     * @throws CommunicationException
     */
    protected function onConnectionError(ErrorCode $errorCode): void
    {
        CommunicationException::handle(
            new ConnectionException($this, static::createExceptionMessage($errorCode->message), $errorCode->value)
        );
    }

    protected static function createExceptionMessage(string $message): string
    {
        // TODO: message format with request info
        return $message;
    }

    protected function beforeSend(): void
    {
        if ($this->routeResolver instanceof RefreshableRouteResolverInterface) {
            $this->routeResolver->refresh();
        }
    }

    protected function afterSend(): void
    {
    }
}
