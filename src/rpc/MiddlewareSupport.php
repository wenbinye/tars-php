<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerInterface;
use wenbinye\tars\rpc\middleware\MiddlewareInterface;

trait MiddlewareSupport
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * @var MiddlewareStack|null
     */
    private $middlewareStack;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        $this->middlewareStack = null;

        return $this;
    }

    private function buildMiddlewareStack(callable $finalHandler): MiddlewareStack
    {
        if (null === $this->middlewareStack) {
            $this->middlewareStack = new MiddlewareStack($this->middlewares, $finalHandler, $this->logger);
        }

        return $this->middlewareStack->withFinalHandler($finalHandler);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
