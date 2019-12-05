<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class StructType extends AbstractType
{
    /**
     * @var string
     */
    private $className;

    /**
     * StructType constructor.
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function asStructType(): StructType
    {
        return $this;
    }

    public function isStruct(): bool
    {
        return true;
    }

    public function asTarsType(): int
    {
        return \TARS::STRUCT;
    }
}
