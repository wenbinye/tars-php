<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnValue;

class MethodMetadata
{
    /**
     * @var string
     */
    private $namespace;
    /**
     * @var string
     */
    private $className;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var string
     */
    private $servantName;
    /**
     * @var TarsParameter[]
     */
    private $parameters;
    /**
     * @var TarsReturnValue[]
     */
    private $returnValues;

    /**
     * MethodMetadata constructor.
     *
     * @param TarsParameter[]   $parameters
     * @param TarsReturnValue[] $returnValues
     */
    public function __construct(string $className, string $namespace, string $methodName, string $servantName, array $parameters, array $returnValues)
    {
        $this->className = $className;
        $this->namespace = $namespace;
        $this->methodName = $methodName;
        $this->servantName = $servantName;
        $this->parameters = $parameters;
        $this->returnValues = $returnValues;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    /**
     * @return TarsParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return TarsReturnValue[]
     */
    public function getReturnValues(): array
    {
        return $this->returnValues;
    }
}
