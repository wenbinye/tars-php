<?php

namespace wenbinye\tars\protocol\type;

class VectorType extends AbstractType
{
    /**
     * @var Type
     */
    private $subType;

    /**
     * VectorType constructor.
     */
    public function __construct(Type $subType)
    {
        $this->subType = $subType;
    }

    public function getSubType(): Type
    {
        return $this->subType;
    }

    public function isVector(): bool
    {
        return true;
    }

    public function asVectorType(): VectorType
    {
        return $this;
    }

    public function asTarsType(): int
    {
        return \TARS::VECTOR;
    }

    public function __toString()
    {
        return sprintf('vector<%s>', $this->subType);
    }
}
