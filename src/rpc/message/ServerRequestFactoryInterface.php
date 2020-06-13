<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\ServerRequestInterface;

interface ServerRequestFactoryInterface
{
    /**
     * Create server request.
     */
    public function create(string $requestBody): ServerRequestInterface;
}
