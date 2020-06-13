<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\exception\TimedOutException;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\Response;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\stat\StatInterface;

class SendStat implements MiddlewareInterface
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
            $responseTime = (int) (1000 * (microtime(true) - $time));
            if ($response->isSuccess()) {
                $this->stat->success($response, $responseTime);
            } else {
                $this->stat->fail($response, $responseTime);
            }

            return $response;
        } catch (TimedOutException $e) {
            $request = $request->withAttribute(RequestAttribute::SERVER_ADDR,
                $e->getConnection()->getAddress()->getAddress());
            $this->stat->timedOut(new Response($request, '', 0, []),
                intval(1000 * (microtime(true) - $time)));
            throw $e;
        }
    }
}
