<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\protocol\annotation\TarsParameterType;
use wenbinye\tars\protocol\annotation\TarsReturnType;

interface QueryFServant
{
    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsReturnType(type = "vector<EndpointF>")
     */
    public function findObjectById(string $id);

    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsParameterType(name = "activeEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsParameterType(name = "inactiveEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsReturnType(type = "int")
     */
    public function findObjectById4Any(string $id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsParameterType(name = "activeEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsParameterType(name = "inactiveEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsReturnType(type = "int")
     */
    public function findObjectById4All(string $id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsParameterType(name = "activeEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsParameterType(name = "inactiveEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsReturnType(type = "int")
     */
    public function findObjectByIdInSameGroup(string $id, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsParameterType(name = "sStation", type = "string", routeKey = false)
     * @TarsParameterType(name = "activeEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsParameterType(name = "inactiveEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsReturnType(type = "int")
     */
    public function findObjectByIdInSameStation(string $id, string $sStation, &$activeEp, &$inactiveEp);

    /**
     * @TarsParameterType(name = "id", type = "string", routeKey = false)
     * @TarsParameterType(name = "setId", type = "string", routeKey = false)
     * @TarsParameterType(name = "activeEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsParameterType(name = "inactiveEp", type = "vector<EndpointF>", routeKey = false)
     * @TarsReturnType(type = "int")
     */
    public function findObjectByIdInSameSet(string $id, string $setId, &$activeEp, &$inactiveEp);
}
