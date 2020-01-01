<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(servant="tars.tarsstat.StatObj")
 */
interface StatFServant
{
    /**
     * @TarsParameter(name = "msg", type = "map<StatMicMsgHead, StatMicMsgBody>")
     * @TarsParameter(name = "bFromClient", type = "bool")
     * @TarsReturnType(type = "int")
     *
     * @param array $msg
     * @param bool  $bFromClient
     *
     * @return int
     */
    public function reportMicMsg($msg, $bFromClient);

    /**
     * @TarsParameter(name = "msg", type = "vector<StatSampleMsg>")
     * @TarsReturnType(type = "int")
     *
     * @param array $msg
     *
     * @return int
     */
    public function reportSampleMsg($msg);
}
