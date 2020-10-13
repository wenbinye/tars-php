<?php

declare(strict_types=1);

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

    public function __toString(): string
    {
        return sprintf('vector<%s>', (string) $this->subType);
    }
}
