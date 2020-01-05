<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(name="tars.tarsproperty.PropertyObj")
 */
interface PropertyFServant
{
    /**
     * @TarsParameter(name = "statmsg", type = "map<StatPropMsgHead, StatPropMsgBody>")
     * @TarsReturnType(type = "int")
     *
     * @param \wenbinye\tars\protocol\type\StructMap $statmsg
     *
     * @return int
     */
    public function reportPropMsg($statmsg);
}
