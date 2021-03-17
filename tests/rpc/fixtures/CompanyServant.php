<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\fixtures;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(name="PHPTest.PHPTcpServer.obj")
 */
interface CompanyServant
{
    /**
     * @TarsReturnType(type = "Company")
     *
     * @return Company
     */
    public function find(): Company;
}
