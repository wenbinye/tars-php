<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\exception\RetryableException;
use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\route\RefreshableServerAddressHolderInterface;
use wenbinye\tars\rpc\route\RegistryRouteResolver;

class Retry implements ClientMiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var RegistryRouteResolver|null
     */
    private $routeResolver;

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
    public function __construct(?RegistryRouteResolver $routeResolver, int $retries = 5, int $initialInterval = 100)
    {
        $this->routeResolver = $routeResolver;
        $this->retries = $retries;
        $this->initialInterval = $initialInterval * 1000;
    }

    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        $retries = $this->retries;
        while ($retries > 0) {
            try {
                return $next($request);
            } catch (RetryableException $e) {
                if ($retries <= 1) {
                    throw $e;
                }
                $this->resetAddress($request);
                $this->logger->warning(static::TAG."retry $request: ".get_class($e).' '.$e->getMessage());
                usleep($this->initialInterval * (2 ** ($this->retries - $retries)));
                --$retries;
            }
        }
        throw new \LogicException('Cannot arrive here');
    }

    protected function resetAddress(RequestInterface $request): void
    {
        if (null !== $this->routeResolver) {
            $this->routeResolver->clear($request->getServantName());
        }
        $address = $request->getAttribute(RequestAttribute::SERVER_ADDR);
        if ($address instanceof RefreshableServerAddressHolderInterface) {
            $address->refresh(true);
        }
    }
}
