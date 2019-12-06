<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\exception\CommunicationException;

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

    public function getParameters(): ParametersInterface;

    /**
     * @throws CommunicationException
     */
    public function send(RequestInterface $request): string;
}
