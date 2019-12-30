<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface MiddlewareInterface
{
    public function __invoke(RequestInterface $request, callable $next): ResponseInterface;
}
