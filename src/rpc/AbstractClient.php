<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;

abstract class AbstractClient
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
     * @var TypeParser
     */
    private $parser;
    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * AbstractClient constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(ConnectionInterface $connection,
                                PackerInterface $packer,
                                RequestFactoryInterface $requestFactory,
                                MethodMetadataFactoryInterface $methodMetadataFactory,
                                ErrorHandlerInterface $errorHandler,
                                array $middlewares = [])
    {
        $this->packer = $packer;
        $this->requestFactory = $requestFactory;
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->errorHandler = $errorHandler;
        $this->middlewareStack = new MiddlewareStack($middlewares, function (RequestInterface $request) use ($connection) {
            $rawContent = $connection->send($request);

            return new Response($rawContent, $request->withAttribute('route', $connection->getRoute()));
        });
        $this->parser = new TypeParser();
    }

    protected function _send(string $method, ...$args): array
    {
        $methodMetadata = $this->methodMetadataFactory->create($this, $method);
        $payload = [];
        foreach ($methodMetadata->getParameters() as $i => $parameter) {
            /* @var TarsParameter $parameter */
            $payload[$parameter->name] = $this->packer->pack($this->parser->parse($parameter->type, $methodMetadata->getNamespace()),
                $parameter->name, $args[$i] ?? null, $this->requestFactory->getVersion());
        }

        $request = $this->requestFactory->createRequest($methodMetadata->getServantName(), $method, $payload);
        $response = $this->middlewareStack->__invoke($request);
        if ($response->isSuccess()) {
            return $this->errorHandler->handle($request, $response);
        }
        $result = [];
        $payload = $response->getPayload();
        foreach ($methodMetadata->getOutputParameters() as $outputParameter) {
            $type = $this->parser->parse($outputParameter->type, $methodMetadata->getNamespace());
            $result[] = $this->packer->unpack($type, $outputParameter->name, $payload, $request->getVersion());
        }
        if (null !== $methodMetadata->getReturnType()) {
            $type = $this->parser->parse($methodMetadata->getReturnType()->type, $methodMetadata->getNamespace());
            if (!$type->isVoid()) {
                $result[] = $this->packer->unpack($type, '', $payload, $request->getVersion());
            }
        }

        return $result;
    }
}
