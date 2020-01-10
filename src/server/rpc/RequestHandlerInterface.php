<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\rpc\message\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * Handle request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
