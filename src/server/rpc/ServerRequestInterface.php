<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\rpc\RequestInterface;

interface ServerRequestInterface extends RequestInterface
{
    public function getPayload(): string;
}
