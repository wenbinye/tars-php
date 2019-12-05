<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ClientInterface
{
    public function send(RequestInterface $request);
}
