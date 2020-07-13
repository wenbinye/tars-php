<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\server;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;
use wenbinye\tars\rpc\message\ServerResponse;

class DefaultErrorHandler implements ErrorHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    public function handle(ServerRequestInterface $request, \Throwable $error): ResponseInterface
    {
        $this->logger->error(static::TAG.sprintf('process %s#%s failed: %s',
                $request->getServantName(), $request->getFuncName(), $error));
        $serverResponse = new ServerResponse($request, []);
        $serverResponse->getResponsePacketBuilder()
            ->setReturnCode($this->getErrorCode($error))
            ->setResultDesc($error->getMessage());

        return $serverResponse;
    }

    protected function getErrorCode(\Throwable $error): int
    {
        if ($error instanceof \InvalidArgumentException) {
            return ErrorCode::INVALID_ARGUMENT;
        }

        return is_numeric($error->getCode()) && 0 !== $error->getCode()
            ? (int) $error->getCode()
            : ErrorCode::UNKNOWN;
    }
}
