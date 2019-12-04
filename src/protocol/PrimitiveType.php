<?php

namespace wenbinye\tars\protocol;

class PrimitiveType extends AbstractType
{
    /**
     * @var int
     */
    private $primitiveType;

    /**
     * PrimitiveType constructor.
     */
    public function __construct(int $primitiveType)
    {
        $this->primitiveType = $primitiveType;
    }

    public function asPrimitiveType(): PrimitiveType
    {
        return $this;
    }

    public function isPrimitive(): bool
    {
        return true;
    }
}
