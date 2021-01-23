<?php

declare(strict_types=1);

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0
 */

namespace wenbinye\tars\client;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(name="tars.tarsnode.ServerObj")
 */
interface ServerFServant
{
    /**
     * 向node定时上报serverInfo.
     *
     * @tars-param serverInfo  服务状态
     * @tars-return int
     *
     * @TarsParameter(name = "serverInfo", type = "ServerInfo")
     * @TarsReturnType(type = "int")
     *
     * @param ServerInfo $serverInfo
     *
     * @return int
     */
    public function keepAlive(ServerInfo $serverInfo): int;

    /**
     * 向node上报TARS版本信息.
     *
     * @tars-param string  版本信息
     * @tars-return int
     *
     * @TarsParameter(name = "app", type = "string")
     * @TarsParameter(name = "serverName", type = "string")
     * @TarsParameter(name = "version", type = "string")
     * @TarsReturnType(type = "int")
     *
     * @param string $app
     * @param string $serverName
     * @param string $version
     *
     * @return int
     */
    public function reportVersion(string $app, string $serverName, string $version): int;
}
