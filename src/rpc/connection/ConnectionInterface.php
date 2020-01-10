<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use wenbinye\tars\rpc\exception\CommunicationException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\Route;

interface ConnectionInterface
{
    /**
     * Opens the connection.
     */
    public function connect(): void;

    /**
     * Closes the connection.
     */
    public function disconnect(): void;

    /**
     * Checks if the connection is considered open.
     */
    public function isConnected(): bool;

    /**
     * Gets the connect info.
     */
    public function getRoute(): Route;

    /**
     * @throws CommunicationException
     */
    public function send(RequestInterface $request): string;
}
