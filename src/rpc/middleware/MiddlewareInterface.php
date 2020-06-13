<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

interface MiddlewareInterface
{
    public function process(RequestInterface $request, callable $next): ResponseInterface;
}
