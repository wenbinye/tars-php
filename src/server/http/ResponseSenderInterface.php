<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

interface ResponseSenderInterface
{
    /**
     * 发送 psr-7 http response.
     */
    public function send(ResponseInterface $response, Response $swooleResponse): void;
}
