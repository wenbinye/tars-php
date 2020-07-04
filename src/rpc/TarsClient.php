<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\RequestFactoryInterface;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\message\ReturnValueInterface;
use wenbinye\tars\rpc\middleware\MiddlewareInterface;

class TarsClient implements TarsClientInterface, LoggerAwareInterface
{
    use MiddlewareSupport;
    /**
     * @var ConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var ErrorHandlerInterface|null
     */
    private $errorHandler;

    /**
     * AbstractClient constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(ConnectionFactoryInterface $connectionFactory,
                                RequestFactoryInterface $requestFactory,
                                ResponseFactoryInterface $responseFactory,
                                ?LoggerInterface $logger,
                                ?ErrorHandlerInterface $errorHandler = null,
                                array $middlewares = [])
    {
        $this->requestFactory = $requestFactory;
        $this->connectionFactory = $connectionFactory;
        $this->responseFactory = $responseFactory;
        $this->setLogger($logger ?? new NullLogger());
        $this->errorHandler = $errorHandler;
        $this->middlewares = $middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function call($servant, string $method, ...$args): array
    {
        return $this->send($this->createRequest($servant, $method, $args));
    }

    public static function builder(): TarsClientBuilder
    {
        return new TarsClientBuilder();
    }

    protected function send(RequestInterface $request): array
    {
        $connection = $this->connectionFactory->create($request->getServantName());
        $request = $request->withAttribute(RequestAttribute::SERVER_ADDR,
            $connection->getAddress()->getAddress());
        $response = $this->buildMiddlewareStack(function (RequestInterface $request) use ($connection) {
            $rawContent = $connection->send($request);

            $response = $this->responseFactory->create($rawContent, $request);
            if (isset($this->errorHandler) && !$response->isSuccess()) {
                return $this->errorHandler->handle($response);
            }

            return $response;
        })->__invoke($request);

        return array_map(static function (ReturnValueInterface $value) {
            return $value->getData();
        }, $response->getReturnValues());
    }

    /**
     * @param object $servant
     */
    protected function createRequest($servant, string $method, array $args): RequestInterface
    {
        return $this->requestFactory->createRequest($servant, $method, $args);
    }
}
