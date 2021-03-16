<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

class History implements ClientMiddlewareInterface
{
    /**
     * @var array
     */
    private $histories = [];

    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        $response = null;
        try {
            $response = $next($request);

            return $response;
        } finally {
            $this->histories[] = [
                'request' => $request,
                'response' => $response,
            ];
        }
    }

    /**
     * @return array
     */
    public function getHistories(): array
    {
        return $this->histories;
    }
}
