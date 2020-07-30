<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

class ErrorHandler implements ClientMiddlewareInterface
{
    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $next($request);
        if (!$response->isSuccess()) {
            return $this->errorHandler->handle($response);
        } else {
            return $response;
        }
    }
}
