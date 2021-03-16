<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\ServerAddressHolder;
use wenbinye\tars\rpc\route\ServerAddressHolderInterface;

class TestConnection implements ConnectionInterface
{
    /**
     * @var callable
     */
    private $requestHandler;

    /**
     * TestConnection constructor.
     *
     * @param callable $requestHandler
     */
    public function __construct(callable $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    public function connect(): void
    {
    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function getAddressHolder(): ServerAddressHolderInterface
    {
        return new ServerAddressHolder(Route::fromString('PHPTest.PHPTcpServer.obj@tcp -h 127.0.0.1 -p 9527 -t 60000'));
    }

    public function send(RequestInterface $request): string
    {
        return call_user_func($this->requestHandler, $request);
    }

    public function recv(float $timeout = null): string
    {
        return '';
    }

    public function setOptions(array $options): void
    {
    }
}
