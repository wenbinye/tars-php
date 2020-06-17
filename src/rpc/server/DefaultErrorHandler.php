<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\server;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;
use wenbinye\tars\rpc\message\ServerResponse;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    public function handle(ServerRequestInterface $request, \Throwable $error): ResponseInterface
    {
        $serverResponse = new ServerResponse($request, []);
        $serverResponse->getResponsePacketBuilder()
            ->setReturnCode($error->getCode() > 0 ? $error->getCode() : ErrorCode::UNKNOWN)
            ->setResultDesc($error->getMessage());

        return $serverResponse;
    }
}
