<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;

interface ServerRequestFactoryInterface
{
    /**
     * 转换 swoole request 对象为 psr-7 http request.
     */
    public function createServerRequest(Request $swooleRequest): ServerRequestInterface;
}
