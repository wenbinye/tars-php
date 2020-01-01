<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

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
     * @var TarsParameter[]
     */
    private $outputParameters;

    /**
     * @var TarsReturnType
     */
    private $returnType;

    /**
     * MethodMetadata constructor.
     *
     * @param TarsParameter[]  $parameters
     * @param TarsReturnType[] $returnValues
     */
    public function __construct(string $className, string $namespace, string $methodName,
                                string $servantName, array $parameters, array $outputParameters, ?TarsReturnType $returnType = null)
    {
        $this->className = $className;
        $this->namespace = $namespace;
        $this->methodName = $methodName;
        $this->servantName = $servantName;
        $this->parameters = $parameters;
        $this->outputParameters = $outputParameters;
        $this->returnType = $returnType;
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
     * @return TarsParameter[]
     */
    public function getOutputParameters(): array
    {
        return $this->outputParameters;
    }

    public function getReturnType(): TarsReturnType
    {
        return $this->returnType;
    }
}
