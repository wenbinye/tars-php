<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\rpc\AbstractClient;

class QueryFClient extends AbstractClient implements QueryFServant
{
    /**
     * {@inheritdoc}
     */
    public function findObjectById($id)
    {
        list($ret) = $this->_call(__FUNCTION__, $id);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectById4Any($id, &$activeEp, &$inactiveEp)
    {
        list($activeEp, $inactiveEp, $ret) = $this->_call(__FUNCTION__, $id);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectById4All($id, &$activeEp, &$inactiveEp)
    {
        list($activeEp, $inactiveEp, $ret) = $this->_call(__FUNCTION__, $id);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectByIdInSameGroup($id, &$activeEp, &$inactiveEp)
    {
        list($activeEp, $inactiveEp, $ret) = $this->_call(__FUNCTION__, $id);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectByIdInSameStation($id, $sStation, &$activeEp, &$inactiveEp)
    {
        list($activeEp, $inactiveEp, $ret) = $this->_call(__FUNCTION__, $id, $sStation);

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function findObjectByIdInSameSet($id, $setId, &$activeEp, &$inactiveEp)
    {
        list($activeEp, $inactiveEp, $ret) = $this->_call(__FUNCTION__, $id, $setId);

        return $ret;
    }
}
