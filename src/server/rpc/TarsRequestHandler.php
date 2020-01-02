<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\MethodMetadataFactory;
use wenbinye\tars\rpc\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\MiddlewareInterface;
use wenbinye\tars\rpc\MiddlewareStack;
use wenbinye\tars\rpc\ResponseInterface;
use wenbinye\tars\rpc\RpcPacker;

class TarsRequestHandler implements RequestHandlerInterface
{
    /**
     * @var array
     */
    private $servants;
    /**
     * @var RpcPacker
     */
    private $packer;
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
        $this->packer = new RpcPacker($packer);
        $this->methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $this->middlewareStack = new MiddlewareStack($middlewares, [$this, 'invoke']);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (isset($this->servants[$request->getServantName()])) {
            $servant = $this->servants[$request->getServantName()];
            $methodMetadata = $this->methodMetadataFactory->create($servant, $request->getMethodName());
            $request = $request->withAttribute('servant', $servant)
                ->withParameters($this->packer->unpackRequest($methodMetadata, $request->getPayload(), $request->getVersion()));
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
        $parameters[] = call_user_func_array([$servant, $request->getMethodName()], $parameters);
        $methodMetadata = $this->methodMetadataFactory->create($servant, $request->getMethodName());

        return new ServerResponse($request, $this->packer->packResponse($methodMetadata, $parameters, $request->getVersion()),
            ErrorCode::SERVER_SUCCESS, 'ok');
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
