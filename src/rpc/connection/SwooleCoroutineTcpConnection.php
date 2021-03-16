<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Swoole\Coroutine\Client;

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

    protected function doRecv(?float $timeout)
    {
        return $this->getResource()->recv($timeout ?? $this->settings[self::RECV_TIMEOUT]);
    }
}
