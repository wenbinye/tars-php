<?php

declare(strict_types=1);

namespace wenbinye\tars\server\fixtures;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(name="PHPTest.PHPTcpServer.obj")
 */
interface HelloServant
{
    /**
     * @TarsParameter(name="message", type="string")
     * @TarsReturnType(type="string")
     *
     * @param string $message
     *
     * @return string
     */
    public function hello(string $message): string;
}
