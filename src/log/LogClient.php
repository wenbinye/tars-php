<?php

declare(strict_types=1);

namespace wenbinye\tars\log;

use wenbinye\tars\rpc\AbstractClient;

class LogClient extends AbstractClient implements LogServant
{
    /**
     * {@inheritdoc}
     */
    public function logger($app, $server, $file, $format, $buffer)
    {
        $this->_send(__FUNCTION__, $app, $server, $file, $format, $buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function loggerbyInfo($info, $buffer)
    {
        $this->_send(__FUNCTION__, $info, $buffer);
    }
}
