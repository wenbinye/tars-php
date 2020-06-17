<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use Psr\Container\ContainerInterface;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\RequestException;
use wenbinye\tars\rpc\message\tup\RequestPacket;
use wenbinye\tars\rpc\TarsRpcPacker;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var MethodMetadataFactoryInterface
     */
    private $methodMetadataFactory;
    /**
     * @var TarsRpcPacker
     */
    private $packer;
    /**
     * @var array
     */
    private $servants;

    public function __construct(
        ContainerInterface $container,
        PackerInterface $packer,
        MethodMetadataFactoryInterface $methodMetadataFactory,
        array $servants = [])
    {
        $this->container = $container;
        $this->packer = new TarsRpcPacker($packer);
        $this->methodMetadataFactory = $methodMetadataFactory;
        $this->servants = $servants;
    }

    public function register(string $servantName, string $servantInterface): void
    {
        $this->servants[$servantName] = $servantInterface;
    }

    public function create(string $requestBody): ServerRequestInterface
    {
        $requestPacket = RequestPacket::parse($requestBody);
        $servantInterface = $this->servants[$requestPacket->getServantName()] ?? null;
        if (!isset($servantInterface) || !$this->container->has($servantInterface)) {
            throw new RequestException($requestPacket, 'Unknown servant '.$requestPacket->getServantName(), ErrorCode::SERVER_NO_SERVANT_ERR);
        }
        $servant = $this->container->get($servantInterface);
        if (!method_exists($servant, $requestPacket->getFuncName())) {
            throw new RequestException($requestPacket, 'Unknown function '.$requestPacket->getServantName().'::'.$requestPacket->getFuncName(), ErrorCode::SERVER_NO_FUNC_ERR);
        }
        $methodMetadata = $this->methodMetadataFactory->create($servant, $requestPacket->getFuncName());
        $parameters = $this->packer->unpackRequest($methodMetadata, $requestPacket->getBuffer(), $requestPacket->getVersion());

        return new ServerRequest($servant, $methodMetadata, $requestPacket, $parameters);
    }
}
