<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\exception\CommunicationException;
use wenbinye\tars\rpc\exception\ConnectionException;

abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var ParametersInterface
     */
    private $parameters;

    private $resource;

    /**
     * AbstractConnection constructor.
     */
    public function __construct(ParametersInterface $parameters)
    {
        $this->parameters = $parameters;
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
    public function getParameters(): ParametersInterface
    {
        return $this->parameters;
    }

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
    }
}
