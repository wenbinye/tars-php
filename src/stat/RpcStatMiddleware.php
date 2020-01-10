<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\rpc\exception\TimedOutException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\Response;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\MiddlewareInterface;

class RpcStatMiddleware implements MiddlewareInterface
{
    /**
     * @var StatInterface
     */
    private $stat;

    public function __construct(StatInterface $stat)
    {
        $this->stat = $stat;
    }

    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $time = microtime(true);
        try {
            /** @var ResponseInterface $response */
            $response = $next($request);
            $responseTime = 1000 * (microtime(true) - $time);
            if ($response->isSuccess()) {
                $this->stat->success($response, $responseTime);
            } else {
                $this->stat->fail($response, $responseTime);
            }

            return $response;
        } catch (TimedOutException $e) {
            $this->stat->timedOut(new Response('', $request->withAttribute('route', $e->getConnection()->getRoute())),
                1000 * (microtime(true) - $time));
            throw $e;
        }
    }
}
