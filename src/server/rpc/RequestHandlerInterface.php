<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\rpc\ResponseInterface;

interface RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
