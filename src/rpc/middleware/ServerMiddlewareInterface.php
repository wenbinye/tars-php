<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;

interface ServerMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface;
}
