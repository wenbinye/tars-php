<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerInterface;

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

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
            if ($this->logger) {
                $this->middlewareStack->setLogger($this->logger);
            }
        }

        return $this->middlewareStack;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
