<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;

/**
 * Formats log messages using variable substitutions for requests, responses,
 * and other transactional data.
 *
 * The following variable substitutions are supported:
 *
 * - $remote_addr:    Client address
 * - $time_local:     Time
 * - $request:        Servant name and function name
 * - $status:         0 success, other fail
 * - $body_bytes_sent: Response body bytes
 * - $request_time:    Request time
 * - $request_id:      Request id
 * - $servant:         Servant name
 * - $method:          Method name
 */
class ServerRequestLog extends AbstractRequestLog implements ServerMiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        return $this->handle($request, $next);
    }
}
