<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ClientRequestInterface extends RequestInterface
{
    const DEFAULT_TIMEOUT = 2000;

    public function getTimeout(): int;
}
