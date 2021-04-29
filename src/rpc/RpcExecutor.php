<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\RequestAttribute;

class RpcExecutor
{
    /**
     * @var object
     */
    private $servantProxy;

    /**
     * @var TarsClient
     */
    private $client;

    /**
     * @var string
     */
    private $method;

    /**
     * @var float|null
     */
    private $timeout;

    /**
     * @var array|null
     */
    private $status;

    /**
     * @var RequestFilterInterface|null
     */
    private $requestFilter;

    /**
     * RpcExecutor constructor.
     *
     * @param object     $servantProxy
     * @param TarsClient $client
     * @param string     $method
     */
    public function __construct(object $servantProxy, TarsClient $client, string $method)
    {
        $this->servantProxy = $servantProxy;
        $this->client = $client;
        $this->method = $method;
    }

    public function withTimeout(float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function withStatus(array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function withRequestFilter(RequestFilterInterface $requestFilter): self
    {
        $this->requestFilter = $requestFilter;

        return $this;
    }

    /**
     * if the method has parameter passed by reference, return array with output parameters
     * and return value, otherwise only return the return value.
     *
     * @param mixed ...$args
     *
     * @return array|mixed|null
     *
     * @throws \ReflectionException
     * @throws exception\ServerException
     */
    public function execute(...$args)
    {
        $request = $this->client->createRequest($this->servantProxy, $this->method, $args);
        if (null !== $this->timeout) {
            $request = RequestAttribute::setRequestTimeout($request, $this->timeout);
        }
        /* @var ClientRequestInterface $request */
        if (null !== $this->status) {
            $request = $request->withStatus(array_merge($this->status, $request->getStatus()));
        }
        if (null !== $this->requestFilter) {
            $request = $this->requestFilter->filter($request);
        }
        $result = $this->client->send($request);
        if (count($result) > 0) {
            $class = ServantProxyGenerator::getServantInterface(get_class($this->servantProxy));
            if (null === $class) {
                throw new \InvalidArgumentException('Cannot get servant for '.get_class($this->servantProxy));
            }
            $hasReference = false;
            $reflectionMethod = $class->getMethod($this->method);

            foreach ($reflectionMethod->getParameters() as $i => $parameter) {
                if ($parameter->isPassedByReference()) {
                    $hasReference = true;
                }
            }
            if ($hasReference) {
                return $result;
            }
            if (null !== $reflectionMethod->getReturnType()) {
                return end($result);
            }
        }

        return null;
    }
}
