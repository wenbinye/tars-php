<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

class MethodMetadata implements MethodMetadataInterface
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
                                string $servantName, array $parameters, ?TarsReturnType $returnType = null)
    {
        $this->className = $className;
        $this->namespace = $namespace;
        $this->methodName = $methodName;
        $this->servantName = $servantName;
        $this->parameters = $parameters;
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

    public function getReturnType(): TarsReturnType
    {
        return $this->returnType;
    }

    public static function dummy(): MethodMetadata
    {
        static $dummy;
        if (!$dummy) {
            $dummy = new MethodMetadata('', '', '', '', []);
        }

        return $dummy;
    }
}
