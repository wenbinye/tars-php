<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

trait MiddlewareSupport
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        if ($this->middlewareStack) {
            throw new \InvalidArgumentException('Cannot add middleware');
        }
        $this->middlewares[] = $middleware;

        return $this;
    }

    private function buildMiddlewareStack(callable $finalHandler): MiddlewareStack
    {
        if (!$this->middlewareStack) {
            $this->middlewareStack = new MiddlewareStack($this->middlewares, $finalHandler);
        }

        return $this->middlewareStack;
    }
}
