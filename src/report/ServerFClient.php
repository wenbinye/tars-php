<?php

declare(strict_types=1);

namespace wenbinye\tars\report;

use wenbinye\tars\rpc\AbstractClient;

class ServerFClient extends AbstractClient implements ServerFServant
{
    /**
     * {@inheritdoc}
     */
    public function keepAlive($serverInfo)
    {
        list($ret) = $this->_send(__FUNCTION__, $serverInfo);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function reportVersion($app, $serverName, $version)
    {
        list($ret) = $this->_send(__FUNCTION__, $app, $serverName, $version);

        return $ret;
    }
}
