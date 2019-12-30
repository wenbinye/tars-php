<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\rpc\AbstractClient;

class StatFClient extends AbstractClient implements StatFServant
{
    /**
     * {@inheritdoc}
     */
    public function reportMicMsg($msg, $bFromClient)
    {
        list($ret) = $this->_send(__FUNCTION__, $msg, $bFromClient);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function reportSampleMsg($msg)
    {
        list($ret) = $this->_send(__FUNCTION__, $msg);

        return $ret;
    }
}
