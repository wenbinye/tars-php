<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\fixtures;

use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

/**
 * @TarsClient(name="PHPTest.PHPTcpServer.FooObj")
 */
interface FooServant
{
    /**
     * @TarsParameter(name = "message", type = "string")
     * @TarsReturnType(type = "void")
     *
     * @param string $message
     */
    public function notReturn($message);

    /**
     * @TarsParameter(name = "message", type = "string")
     * @TarsReturnType(type = "void")
     *
     * @param string $message
     */
    public function voidReturn(string $message): void;

    /**
     * @TarsParameter(name = "message", type = "string")
     * @TarsReturnType(type = "string")
     *
     * @param string $message
     */
    public function stringReturn(string $message): string;

    /**
     * @TarsParameter(name = "message", type = "string")
     * @TarsReturnType(type = "Foo")
     *
     * @param string $message
     *
     * @return Foo
     */
    public function objReturn(string $message): Foo;
}
