<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Swoole\Coroutine\Client;
use wenbinye\tars\rpc\ErrorCode;

class SwooleCoroutineTcpConnection extends SwooleTcpConnection
{
    protected const TAG = '['.__CLASS__.'] ';

    /**
     * {@inheritdoc}
     */
    protected function createSwooleClient()
    {
        return new Client(SWOOLE_TCP);
    }

    public function recv(): string
    {
        $client = $this->getResource();
        if (null === $client) {
            return '';
        }
        $response = $client->recv($this->settings[self::RECV_TIMEOUT] ?? 5.0);
        $errCode = $client->errCode;
        if ('' === $response || false === $response) {
            $this->destroyResource();
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
                isset($errCode) ? socket_strerror($errCode) : null);
        }

        return $response;
    }
}
