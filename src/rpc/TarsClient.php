<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\message\RequestFactoryInterface;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\message\ReturnValueInterface;

class TarsClient implements TarsClientInterface
{
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * AbstractClient constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(ConnectionFactoryInterface $connectionFactory,
                                RequestFactoryInterface $requestFactory,
                                ResponseFactoryInterface $responseFactory,
                                ?ErrorHandlerInterface $errorHandler = null,
                                array $middlewares = [])
    {
        $this->requestFactory = $requestFactory;
        $this->middlewareStack = new MiddlewareStack($middlewares, static function (RequestInterface $request) use ($connectionFactory, $errorHandler, $responseFactory) {
            $connection = $connectionFactory->create($request->getServantName());
            $rawContent = $connection->send($request);

            $response = $responseFactory->create($rawContent, $request->withAttribute('route', $connection->getRoute()));
            if (isset($errorHandler) && !$response->isSuccess()) {
                return $errorHandler->handle($response);
            }

            return $response;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function call($servant, string $method, ...$args): array
    {
        $request = $this->requestFactory->createRequest($servant, $method, $args);
        $response = $this->middlewareStack->__invoke($request);

        return array_map(static function (ReturnValueInterface $value) {
            return $value->getData();
        }, $response->getReturnValues());
    }
}
