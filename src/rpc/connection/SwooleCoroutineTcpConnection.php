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

    protected function doRecv()
    {
        return $this->getResource()->recv($this->settings[self::RECV_TIMEOUT]);
    }
}
