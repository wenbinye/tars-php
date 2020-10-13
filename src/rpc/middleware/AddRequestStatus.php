<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestHolder;

class AddRequestStatus implements ClientMiddlewareInterface
{
    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        $serverRequest = ServerRequestHolder::getRequest();
        if (null !== $serverRequest) {
            $request = $request->withStatus(array_merge($serverRequest->getStatus(), $request->getStatus()));
        }

        return $next($request);
    }
}
