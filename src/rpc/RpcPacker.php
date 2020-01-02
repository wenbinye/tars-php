<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;

class RpcPacker
{
    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var TypeParser
     */
    private $parser;

    /**
     * RpcPacker constructor.
     */
    public function __construct(PackerInterface $packer)
    {
        $this->packer = $packer;
        $this->parser = new TypeParser();
    }

    public function packRequest(MethodMetadata $method, array $parameters, int $version): array
    {
        $payload = [];
        foreach ($method->getParameters() as $i => $parameter) {
            /* @var TarsParameter $parameter */
            $payload[$parameter->name] = $this->packer->pack($this->parser->parse($parameter->type, $method->getNamespace()),
                $parameter->name, $parameters[$i] ?? null, $version);
        }

        return $payload;
    }

    public function unpackResponse(MethodMetadata $method, string $data, int $version): array
    {
        $result = [];
        foreach ($method->getOutputParameters() as $outputParameter) {
            $type = $this->parser->parse($outputParameter->type, $method->getNamespace());
            $result[] = $this->packer->unpack($type, $outputParameter->name, $data, $version);
        }
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $result[] = $this->packer->unpack($type, '', $data, $version);
            }
        }

        return $result;
    }

    public function unpackRequest(MethodMetadata $method, string $data, int $version): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $parameters[] = $this->packer->unpack($type, $parameter->name, $data, $version);
        }
        foreach ($method->getOutputParameters() as $parameter) {
            $parameters[] = null;
        }

        return $parameters;
    }

    public function packResponse(MethodMetadata $method, array $data, int $version): array
    {
        $result = [];
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $result[''] = $this->packer->pack($type, '', end($data), $version);
            }
        }
        $offset = count($method->getParameters());
        foreach ($method->getOutputParameters() as $i => $outputParameter) {
            $type = $this->parser->parse($outputParameter->type, $method->getNamespace());
            $result[$outputParameter->name] = $this->packer->pack($type, $outputParameter->name, $data[$offset + $i], $version);
        }

        return $result;
    }
}
