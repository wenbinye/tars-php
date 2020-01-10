<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

interface ServerRequestFactoryInterface
{
    /**
     * Create server request.
     */
    public function create(string $requestBody): ServerRequestInterface;
}
