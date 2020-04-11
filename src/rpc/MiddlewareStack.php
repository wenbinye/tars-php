<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

class MiddlewareStack implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MiddlewareInterface
     */
    private $middlewares;
    /**
     * @var callable
     */
    private $final;

    public function __construct(array $middlewares, callable $final)
    {
        $this->middlewares = $middlewares;
        $this->final = $final;
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('call with %d middlewares', count($this->middlewares)));
        }

        return $this->callNext($request, 0);
    }

    private function callNext(RequestInterface $request, int $index): ResponseInterface
    {
        if (!isset($this->middlewares[$index])) {
            return call_user_func($this->final, $request);
        }
        if (is_object($this->middlewares[$index])) {
            $this->logger && $this->logger->debug('invoke middleware '.get_class($this->middlewares[$index]));
        }

        return call_user_func($this->middlewares[$index], $request, function (RequestInterface $request) use ($index) {
            return $this->callNext($request, $index + 1);
        });
    }
}
