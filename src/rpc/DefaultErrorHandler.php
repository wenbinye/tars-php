<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\exception\ServerException;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function handle(RequestInterface $request, int $code, string $message): void
    {
        throw new ServerException($code, $message);
    }
}
