<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\exception\SyntaxErrorException;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;
use wenbinye\tars\rpc\message\MethodMetadataInterface;
use wenbinye\tars\rpc\message\Parameter;
use wenbinye\tars\rpc\message\ParameterInterface;
use wenbinye\tars\rpc\message\ReturnValue;
use wenbinye\tars\rpc\message\ReturnValueInterface;
use wenbinye\tars\rpc\message\tup\Tup;

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

    public function __construct(PackerInterface $packer)
    {
        $this->packer = $packer;
        $this->parser = new TypeParser();
    }

    /**
     * Pack client request parameters.
     *
     * @return ParameterInterface[]
     *
     * @throws SyntaxErrorException
     */
    public function packRequest(MethodMetadataInterface $method, array $parameters, int $version): array
    {
        $paramObjs = [];
        foreach ($method->getParameters() as $order => $parameter) {
            if ($parameter->out) {
                continue;
            }
            $data = $parameters[$order] ?? null;
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $paramObjs[] = new Parameter(
                $order,
                $parameter->name,
                false,
                $data,
                $this->packer->pack($type, $parameter->name, $data, $version));
        }

        return $paramObjs;
    }

    /**
     * Extracts client response result.
     *
     * @return ReturnValueInterface[]
     *
     * @throws SyntaxErrorException
     */
    public function unpackResponse(MethodMetadataInterface $method, string $data, int $version): array
    {
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            if (!$parameter->out) {
                continue;
            }
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $result[] = new ReturnValue(
                $parameter->name,
                $this->packer->unpack($type, $parameter->name, $data, $version),
                null);
        }
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $result[] = new ReturnValue(
                    null,
                    $this->packer->unpack($type, '', $data, $version),
                    null);
            }
        }

        return $result;
    }

    /**
     * Unpack server request parameters.
     *
     * @return ParameterInterface[]
     *
     * @throws SyntaxErrorException
     */
    public function unpackRequest(MethodMetadataInterface $method, string $data, int $version): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $i => $parameter) {
            $order = $parameter->order ?? ($i + 1);
            $paramData = null;
            if (!$parameter->out) {
                $key = Tup::VERSION === $version ? $parameter->name : (string) $order;
                $type = $this->parser->parse($parameter->type, $method->getNamespace());
                $paramData = $this->packer->unpack($type, $key, $data, $version);
            }
            $parameters[] = new Parameter(
                $order,
                $parameter->name,
                $parameter->out ?? false,
                $paramData,
                null);
        }

        return $parameters;
    }

    /**
     * Generates server response.
     *
     * @param array $data method parameters and return value
     *
     * @return ReturnValueInterface[] return packed output value array
     *
     * @throws SyntaxErrorException
     */
    public function packResponse(MethodMetadataInterface $method, array $data, int $version): array
    {
        $result = [];
        if (null !== $method->getReturnType()) {
            $type = $this->parser->parse($method->getReturnType()->type, $method->getNamespace());
            if (!$type->isVoid()) {
                $ret = end($data);
                $result[] = new ReturnValue(
                    null,
                    $ret,
                    $this->packer->pack($type, '', $ret, $version));
            }
        }
        foreach ($method->getParameters() as $i => $parameter) {
            if (!$parameter->out) {
                continue;
            }
            $type = $this->parser->parse($parameter->type, $method->getNamespace());
            $result[] = new ReturnValue(
                $parameter->name,
                $data[$i],
                $this->packer->pack($type, $parameter->name, $data[$i], $version));
        }

        return $result;
    }
}
