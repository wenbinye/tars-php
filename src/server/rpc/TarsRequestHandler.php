<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\MethodMetadataFactory;
use wenbinye\tars\rpc\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\MiddlewareInterface;
use wenbinye\tars\rpc\MiddlewareStack;
use wenbinye\tars\rpc\ResponseInterface;

class TarsRequestHandler implements RequestHandlerInterface
{
    /**
     * @var array
     */
    private $servants;
    /**
     * @var PackerInterface
     */
    private $packer;
    /**
     * @var TypeParser
     */
    private $parser;
    /**
     * @var MethodMetadataFactoryInterface
     */
    private $methodMetadataFactory;
    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * TarsRequestHandler constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(array $servants, Reader $annotationReader, PackerInterface $packer, array $middlewares = [])
    {
        foreach ($servants as $servant) {
            foreach ($this->getServantNames($annotationReader, $servant) as $servantName) {
                $this->servants[$servantName] = $servant;
            }
        }
        $this->packer = $packer;
        $this->parser = new TypeParser();
        $this->methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $this->middlewareStack = new MiddlewareStack($middlewares, [$this, 'invoke']);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (isset($this->servants[$request->getServantName()])) {
            $request = $request->withAttribute('servant', $this->servants[$request->getServantName()]);
            $servant = $request->getAttribute('servant');
            $methodMetadata = $this->methodMetadataFactory->create($servant, $request->getMethodName());
            $payload = $request->getPayload();
            $args = [];
            foreach ($methodMetadata->getParameters() as $parameter) {
                $type = $this->parser->parse($parameter->type, $methodMetadata->getNamespace());
                $args[] = $this->packer->unpack($type, $parameter->name, $payload, $request->getVersion());
            }
            foreach ($methodMetadata->getOutputParameters() as $parameter) {
                $args[] = null;
            }
            $request = $request->withParameters($args);
        }

        return $this->middlewareStack->__invoke($request);
    }

    public function invoke(ServerRequestInterface $request): ResponseInterface
    {
        $servant = $request->getAttribute('servant');
        if (!$servant) {
            return new ServerResponse($request, [], ErrorCode::SERVER_NO_SERVANT_ERR);
        }
        if (!method_exists($servant, $request->getMethodName())) {
            return new ServerResponse($request, [], ErrorCode::SERVER_NO_FUNC_ERR);
        }
        $parameters = $request->getParameters();
        $ret = call_user_func_array([$servant, $request->getMethodName()], $parameters);
        $encodeBuffers = [];
        $methodMetadata = $this->methodMetadataFactory->create($servant, $request->getMethodName());
        if (null !== $methodMetadata->getReturnType()) {
            $type = $this->parser->parse($methodMetadata->getReturnType()->type, $methodMetadata->getNamespace());
            if (!$type->isVoid()) {
                $encodeBuffers[] = $this->packer->pack($type, '', $ret, $request->getVersion());
            }
        }
        $offset = count($methodMetadata->getParameters());
        foreach ($methodMetadata->getOutputParameters() as $i => $outputParameter) {
            $type = $this->parser->parse($outputParameter->type, $methodMetadata->getNamespace());
            $encodeBuffers[] = $this->packer->pack($type, $outputParameter->name, $parameters[$offset + $i], $request->getVersion());
        }

        return new ServerResponse($request, $encodeBuffers, ErrorCode::SERVER_SUCCESS, 'ok');
    }

    private function getServantNames(Reader $annotationReader, $servant): array
    {
        $servantNames = [];
        $reflectionClass = new \ReflectionClass($servant);
        foreach ($reflectionClass->getInterfaces() as $interface) {
            /** @var TarsServant $servantAnnotation */
            $servantAnnotation = $annotationReader->getClassAnnotation($interface, TarsServant::class);
            if ($servantAnnotation) {
                $servantNames[] = $servantAnnotation->servant;
            }
        }

        return $servantNames;
    }
}
