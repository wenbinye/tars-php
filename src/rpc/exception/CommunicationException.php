<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\exception;

use wenbinye\tars\rpc\connection\ConnectionInterface;

abstract class CommunicationException extends \Exception
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(
        ConnectionInterface $connection,
        $message = null,
        $code = null,
        \Exception $cause = null)
    {
        parent::__construct($message, $code, $cause);
        $this->connection = $connection;
    }

    /**
     * Gets the connection that generated the exception.
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Indicates if the receiver should reset the underlying connection.
     */
    public function shouldResetConnection(): bool
    {
        return true;
    }

    /**
     * Helper method to handle exceptions generated by a connection object.
     *
     * @param CommunicationException $exception exception
     *
     * @throws CommunicationException
     */
    public static function handle(CommunicationException $exception): void
    {
        if ($exception->shouldResetConnection()) {
            $connection = $exception->getConnection();

            $connection->reconnect();
        }

        throw $exception;
    }
}
