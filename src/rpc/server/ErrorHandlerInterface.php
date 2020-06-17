<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\server;

use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;

interface ErrorHandlerInterface
{
    public function handle(ServerRequestInterface $request, \Throwable $error): ResponseInterface;
}
