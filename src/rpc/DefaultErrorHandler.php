<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\exception\ServerException;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function handle(RequestInterface $request, ResponseInterface $response): void
    {
        throw new ServerException($response->getReturnCode(), $response->getErrorMessage());
    }
}
