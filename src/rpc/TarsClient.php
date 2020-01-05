<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\PackerInterface;

class TarsClient
{
    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var MethodMetadataFactoryInterface
     */
    private $methodMetadataFactory;

    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;

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
                                PackerInterface $packer,
                                RequestFactoryInterface $requestFactory,
                                MethodMetadataFactoryInterface $methodMetadataFactory,
                                ErrorHandlerInterface $errorHandler,
                                array $middlewares = [])
    {
        $this->packer = new TarsRpcPacker($packer);
        $this->requestFactory = $requestFactory;
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->errorHandler = $errorHandler;
        $this->middlewareStack = new MiddlewareStack($middlewares, static function (RequestInterface $request) use ($connectionFactory) {
            $connection = $connectionFactory->create($request->getServantName());
            $rawContent = $connection->send($request);

            return new Response($rawContent, $request->withAttribute('route', $connection->getRoute()));
        });
    }

    /**
     * @param object $servant
     * @param string $method
     * @param mixed ...$args
     * @return array
     */
    public function send($servant, string $method, ...$args): array
    {
        $methodMetadata = $this->methodMetadataFactory->create($servant, $method);

        $request = $this->requestFactory->createRequest($methodMetadata->getServantName(), $method,
            $this->packer->packRequest($methodMetadata, $args, $this->requestFactory->getVersion()));
        $response = $this->middlewareStack->__invoke($request);
        if (!$response->isSuccess()) {
            return $this->errorHandler->handle($request, $response);
        }

        return $this->packer->unpackResponse($methodMetadata, $response->getPayload(), $response->getVersion());
    }
}
