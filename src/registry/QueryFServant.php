<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(servant="tars.tarsregistry.QueryObj")
 */
interface QueryFServant
{
    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsReturnType(type = "vector<EndpointF>")
     *
     * @param string $id
     *
     * @return array
     */
    public function findObjectById($id);

    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsParameter(name = "activeEp", type = "vector<EndpointF>", out=true)
     * @TarsParameter(name = "inactiveEp", type = "vector<EndpointF>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param string $id
     * @param array  $activeEp
     * @param array  $inactiveEp
     *
     * @return int
     */
    public function findObjectById4Any($id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsParameter(name = "activeEp", type = "vector<EndpointF>", out=true)
     * @TarsParameter(name = "inactiveEp", type = "vector<EndpointF>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param string $id
     * @param array  $activeEp
     * @param array  $inactiveEp
     *
     * @return int
     */
    public function findObjectById4All($id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsParameter(name = "activeEp", type = "vector<EndpointF>", out=true)
     * @TarsParameter(name = "inactiveEp", type = "vector<EndpointF>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param string $id
     * @param array  $activeEp
     * @param array  $inactiveEp
     *
     * @return int
     */
    public function findObjectByIdInSameGroup($id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsParameter(name = "sStation", type = "string")
     * @TarsParameter(name = "activeEp", type = "vector<EndpointF>", out=true)
     * @TarsParameter(name = "inactiveEp", type = "vector<EndpointF>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param string $id
     * @param string $sStation
     * @param array  $activeEp
     * @param array  $inactiveEp
     *
     * @return int
     */
    public function findObjectByIdInSameStation($id, $sStation, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameter(name = "id", type = "string")
     * @TarsParameter(name = "setId", type = "string")
     * @TarsParameter(name = "activeEp", type = "vector<EndpointF>", out=true)
     * @TarsParameter(name = "inactiveEp", type = "vector<EndpointF>", out=true)
     * @TarsReturnType(type = "int")
     *
     * @param string $id
     * @param string $setId
     * @param array  $activeEp
     * @param array  $inactiveEp
     *
     * @return int
     */
    public function findObjectByIdInSameSet($id, $setId, &$activeEp, &$inactiveEp);
}
