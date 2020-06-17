<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\exception\RequestException;

interface ServerRequestFactoryInterface
{
    /**
     * Create server request.
     *
     * @throws RequestException
     */
    public function create(string $requestBody): ServerRequestInterface;
}
