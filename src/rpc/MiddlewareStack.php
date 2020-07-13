<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\middleware\MiddlewareInterface;

class MiddlewareStack implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;
    /**
     * @var callable
     */
    private $final;

    public function __construct(array $middlewares, callable $final, ?LoggerInterface $logger)
    {
        $this->middlewares = $middlewares;
        $this->final = $final;
        $this->setLogger($logger ?? new NullLogger());
    }

    public function withFinalHandler(callable $final): self
    {
        $copy = clone $this;
        $copy->final = $final;

        return $copy;
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        return $this->callNext($request, 0);
    }

    private function callNext(RequestInterface $request, int $index): ResponseInterface
    {
        if (!isset($this->middlewares[$index])) {
            return call_user_func($this->final, $request);
        }

        return call_user_func($this->middlewares[$index], $request, function (RequestInterface $request) use ($index) {
            return $this->callNext($request, $index + 1);
        });
    }
}
