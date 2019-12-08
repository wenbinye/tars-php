<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ErrorHandlerInterface
{
    public function handle(RequestInterface $request, int $code, string $message);
}
