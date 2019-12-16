<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnValue;

/**
 * @TarsClient(servant="tars.tarsstat.StatObj")
 */
interface StatFServant
{
    /**
     * @TarsParameter(name = "msg", type = "map<StatMicMsgHead, StatMicMsgBody>")
     * @TarsParameter(name = "bFromClient", type = "bool")
     * @TarsReturnValue(type = "int")
     *
     * @param array $msg
     * @param bool  $bFromClient
     *
     * @return int
     */
    public function reportMicMsg($msg, $bFromClient);

    /**
     * @TarsParameter(name = "msg", type = "vector<StatSampleMsg>")
     * @TarsReturnValue(type = "int")
     *
     * @param array $msg
     *
     * @return int
     */
    public function reportSampleMsg($msg);
}
