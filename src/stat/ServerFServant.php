<?php

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsServant(servant="tars.tarsnode.ServerObj")
 */
interface ServerFServant {
    /**
     * @TarsParameter(name = "serverInfo", type = "ServerInfo")
     * @TarsReturnType(type = "int")
     *
     * @param \wenbinye\tars\stat\ServerInfo $serverInfo
     * @return int
     */
    public function keepAlive($serverInfo);

    /**
     * @TarsParameter(name = "app", type = "string")
     * @TarsParameter(name = "serverName", type = "string")
     * @TarsParameter(name = "version", type = "string")
     * @TarsReturnType(type = "int")
     *
     * @param string $app
     * @param string $serverName
     * @param string $version
     * @return int
     */
    public function reportVersion($app, $serverName, $version);

}
