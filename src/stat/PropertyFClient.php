<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\rpc\AbstractClient;

class PropertyFClient extends AbstractClient implements PropertyFServant
{
    /**
     * {@inheritdoc}
     */
    public function reportPropMsg($statmsg)
    {
        list($ret) = $this->_send(__FUNCTION__, $statmsg);

        return $ret;
    }
}
