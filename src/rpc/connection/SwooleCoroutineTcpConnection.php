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
        $response = $client->recv($this->settings[self::RECV_TIMEOUT]);
        if (is_string($response) && '' !== $response) {
            return $response;
        }
        $this->logger->error(self::TAG.'receive fail, response='.json_encode($response));
        $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
            isset($client->errCode) ? socket_strerror($client->errCode) : null);
    }
}
