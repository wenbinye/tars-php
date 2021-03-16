<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\exception\RequestIdMismatchException;
use wenbinye\tars\rpc\exception\ServerException;
use wenbinye\tars\rpc\message\ClientRequestFactoryInterface;
use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ReturnValueInterface;

class TarsClient implements TarsClientInterface, LoggerAwareInterface
{
    use MiddlewareSupport;
    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var ConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @var ClientRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(ConnectionFactoryInterface $connectionFactory,
                                ClientRequestFactoryInterface $requestFactory,
                                ResponseFactoryInterface $responseFactory,
                                ?LoggerInterface $logger,
                                array $middlewares = [])
    {
        $this->requestFactory = $requestFactory;
        $this->connectionFactory = $connectionFactory;
        $this->responseFactory = $responseFactory;
        $this->setLogger($logger ?? new NullLogger());
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

    public function send(ClientRequestInterface $request): array
    {
        $connection = $this->connectionFactory->create($request->getServantName());
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $request = RequestAttribute::setServerAddress($request, $connection->getAddressHolder());
        $response = $this->buildMiddlewareStack(function (ClientRequestInterface $request) use ($connection): ResponseInterface {
            $rawContent = $connection->send($request);
            try {
                return $this->responseFactory->create($rawContent, $request);
            } catch (RequestIdMismatchException $e) {
                return $this->checkAndReceive($connection, $request, $e);
            }
        })->__invoke($request);
        if (!$response->isSuccess()) {
            throw new ServerException($response);
        }

        return array_map(static function (ReturnValueInterface $value) {
            return $value->getData();
        }, $response->getReturnValues());
    }

    /**
     * @param object $servant
     * @param string $method
     * @param array  $args
     *
     * @return ClientRequestInterface
     */
    public function createRequest($servant, string $method, array $args): ClientRequestInterface
    {
        return $this->requestFactory->createRequest($servant, $method, $args);
    }

    /**
     * 可能会有响应不匹配的情况，再尝试一次
     *
     * @param connection\ConnectionInterface $connection
     * @param ClientRequestInterface         $request
     * @param RequestIdMismatchException     $e
     *
     * @return ResponseInterface
     */
    protected function checkAndReceive(
        connection\ConnectionInterface $connection, ClientRequestInterface $request, RequestIdMismatchException $e): ResponseInterface
    {
        $this->logger->warning(static::TAG.$e->getMessage());
        try {
            $rawContent = $connection->recv();

            return $this->responseFactory->create($rawContent, $request);
        } catch (RequestIdMismatchException $e) {
            return $this->checkAndReceive($connection, $request, $e);
        }
    }
}
