<?php

declare(strict_types=1);

namespace wenbinye\tars\log;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(servant="tars.tarslog.LogObj")
 */
interface LogServant
{
    /**
     * @TarsParameter(name = "app", type = "string")
     * @TarsParameter(name = "server", type = "string")
     * @TarsParameter(name = "file", type = "string")
     * @TarsParameter(name = "format", type = "string")
     * @TarsParameter(name = "buffer", type = "vector<string>")
     * @TarsReturnType(type = "void")
     *
     * @param string $app
     * @param string $server
     * @param string $file
     * @param string $format
     * @param array  $buffer
     *
     * @return void
     */
    public function logger($app, $server, $file, $format, $buffer);

    /**
     * @TarsParameter(name = "info", type = "LogInfo")
     * @TarsParameter(name = "buffer", type = "vector<string>")
     * @TarsReturnType(type = "void")
     *
     * @param \wenbinye\tars\log\LogInfo $info
     * @param array                      $buffer
     *
     * @return void
     */
    public function loggerbyInfo($info, $buffer);
}
