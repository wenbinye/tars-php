<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\exception\ServerException;
use wenbinye\tars\rpc\message\ResponseInterface;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function handle(ResponseInterface $response)
    {
        if (ErrorCode::INVALID_ARGUMENT === $response->getReturnCode()) {
            throw new \InvalidArgumentException($response->getMessage());
        }
        throw new ServerException($response);
    }
}
