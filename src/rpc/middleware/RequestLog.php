<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

class RequestLog extends AbstractRequestLog implements ClientMiddlewareInterface
{
    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        return $this->handle($request, $next);
    }
}
