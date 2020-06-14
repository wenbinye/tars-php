<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;
use wenbinye\tars\rpc\message\MethodMetadata;
use wenbinye\tars\rpc\message\MethodMetadataInterface;
use wenbinye\tars\rpc\message\Parameter;
use wenbinye\tars\rpc\message\ParameterInterface;
use wenbinye\tars\rpc\message\ReturnValue;
use wenbinye\tars\rpc\message\ReturnValueInterface;

class TarsRpcPacker
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

    /**
     * Pack request parameters.
     *
     * @param MethodMetadata $method
     *
     * @return ParameterInterface[]
     *
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function packRequest(MethodMetadataInterface $method, array $parameters, int $version): array
    {
        $paramObjs = [];
        foreach ($method->getParameters() as $i => $parameter) {
            if ($parameter->out) {
                continue;
            }
            $data = $parameters[$i] ?? null;
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $paramObjs[] = new Parameter($i, $parameter->name, false, $data,
                $this->packer->pack($type, $parameter->name, $data, $version));
        }

        return $paramObjs;
    }

    /**
     * @param MethodMetadata $method
     *
     * @return ReturnValueInterface[]
     *
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function unpackResponse(MethodMetadataInterface $method, string $data, int $version): array
    {
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            if (!$parameter->out) {
                continue;
            }
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $result[] = new ReturnValue($parameter->name,
                $this->packer->unpack($type, $parameter->name, $data, $version), '');
        }
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $result[] = new ReturnValue('', $this->packer->unpack($type, '', $data, $version), '');
            }
        }

        return $result;
    }

    /**
     * Unpack request parameters.
     *
     * @param MethodMetadata $method
     *
     * @return ParameterInterface[]
     *
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function unpackRequest(MethodMetadataInterface $method, string $data, int $version): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $i => $parameter) {
            if ($parameter->out) {
                $parameters[] = new Parameter($parameter->order ?? $i, $parameter->name, true, null, '');
            } else {
                $type = $this->parser->parse($parameter->type, $method->getNamespace());
                $paramData = $this->packer->unpack($type, $parameter->name, $data, $version);

                $parameters[] = new Parameter($parameter->order ?? $i, $parameter->name, false, $paramData, '');
            }
        }

        return $parameters;
    }

    /**
     * Pack server response.
     *
     * @param MethodMetadata $method
     * @param array          $data   method parameters and return value
     *
     * @return ReturnValueInterface[] return packed output value array
     *
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function packResponse(MethodMetadataInterface $method, array $data, int $version): array
    {
        $result = [];
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $ret = end($data);
                $result[] = new ReturnValue('', $ret, $this->packer->pack($type, '', $ret, $version));
            }
        }
        foreach ($method->getParameters() as $i => $parameter) {
            if (!$parameter->out) {
                continue;
            }
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $result[] = new ReturnValue($parameter->name, $data[$i],
                $this->packer->pack($type, $parameter->name, $data[$i], $version));
        }

        return $result;
    }
}
