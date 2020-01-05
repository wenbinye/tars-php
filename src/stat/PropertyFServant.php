<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(servant="tars.tarsproperty.PropertyObj")
 */
interface PropertyFServant
{
    /**
     * @TarsParameter(name = "statmsg", type = "map<StatPropMsgHead, StatPropMsgBody>")
     * @TarsReturnType(type = "int")
     *
     * @param array $statmsg
     *
     * @return int
     */
    public function reportPropMsg($statmsg);
}
