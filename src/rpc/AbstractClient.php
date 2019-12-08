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
     * @var ConnectionInterface
     */
    private $connection;

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
     * AbstractClient constructor.
     */
    public function __construct(ConnectionInterface $connection,
                                PackerInterface $packer,
                                RequestFactoryInterface $requestFactory,
                                MethodMetadataFactoryInterface $methodMetadataFactory,
                                ErrorHandlerInterface $errorHandler)
    {
        $this->packer = $packer;
        $this->requestFactory = $requestFactory;
        $this->connection = $connection;
        $this->parser = new TypeParser();
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->errorHandler = $errorHandler;
    }

    public function _call(string $method, ...$args): array
    {
        $methodMetadata = $this->methodMetadataFactory->create($this, $method);
        $payload = [];
        foreach ($methodMetadata->getParameters() as $i => $parameter) {
            /* @var TarsParameter $parameter */
            $payload[$parameter->name] = $this->packer->pack($this->parser->parse($parameter->type, $methodMetadata->getNamespace()),
                $parameter->name, $args[$i] ?? null, $this->requestFactory->getVersion());
        }

        $request = $this->requestFactory->createRequest($methodMetadata->getServantName(), $method, $payload);
        $response = $this->connection->send($request);
        $decoded = \TUPAPI::decode($response, $request->getVersion());
        if (0 !== $decoded['iRet']) {
            return $this->errorHandler->handle($request, $decoded['iRet'], $decoded['sResultDesc'] ?? '');
        }
        $result = [];
        foreach ($methodMetadata->getReturnValues() as $name => $returnType) {
            $type = $this->parser->parse($returnType->type, $methodMetadata->getNamespace());
            if (!$type->isVoid()) {
                $result[] = $this->packer->unpack($type, $returnType->name, $decoded['sBuffer'], $request->getVersion());
            }
        }

        return $result;
    }
}
