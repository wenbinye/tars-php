<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

interface ClientMiddlewareInterface extends MiddlewareInterface
{
    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface;
}
