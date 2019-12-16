<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnValue;

/**
 * @TarsClient(servant="")
 */
interface PropertyFServant
{
    /**
     * @TarsParameter(name = "statmsg", type = "map<StatPropMsgHead, StatPropMsgBody>")
     * @TarsReturnValue(type = "int")
     *
     * @param array $statmsg
     *
     * @return int
     */
    public function reportPropMsg($statmsg);
}
