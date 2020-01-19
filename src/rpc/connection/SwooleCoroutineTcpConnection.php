<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use Swoole\Coroutine\Client;

class SwooleCoroutineTcpConnection extends SwooleTcpConnection
{
    protected function createSwooleClient()
    {
        return new Client(SWOOLE_TCP);
    }
}
