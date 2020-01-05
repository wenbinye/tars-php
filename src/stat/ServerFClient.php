<?php

namespace wenbinye\tars\stat;

use wenbinye\tars\rpc\AbstractClient;

class ServerFClient extends AbstractClient implements ServerFServant {
    /**
     * @inheritDoc
     */
    public function keepAlive($serverInfo) {
        list($ret) = $this->_send(__FUNCTION__, $serverInfo);
        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function reportVersion($app, $serverName, $version) {
        list($ret) = $this->_send(__FUNCTION__, $app, $serverName, $version);
        return $ret;
    }

}
