<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\exception\RetryableException;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

class Retry implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var int
     */
    private $retries;

    /**
     * @var int
     */
    private $initialInterval;

    /**
     * Retry constructor.
     *
     * @param int $retries         max retry times
     * @param int $initialInterval initial sleep interval in millisecond
     */
    public function __construct(int $retries = 5, int $initialInterval = 100)
    {
        $this->retries = $retries;
        $this->initialInterval = $initialInterval * 1000;
    }

    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        $retries = $this->retries;
        while ($retries > 0) {
            try {
                return $next($request);
            } catch (RetryableException $e) {
                --$retries;
                if ($retries <= 0) {
                    throw $e;
                }
                $this->logger->info(static::TAG.'retry request because '.$e->getMessage());
                usleep($this->initialInterval * ($this->retries - $retries));
            }
        }
        throw new \LogicException('Cannot arrive here');
    }
}
