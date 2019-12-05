<?php

namespace wenbinye\tars\protocol\type;

abstract class AbstractType implements Type
{
    public function isPrimitive(): bool
    {
        return false;
    }

    public function isStruct(): bool
    {
        return false;
    }

    public function isVector(): bool
    {
        return false;
    }

    public function isMap(): bool
    {
        return false;
    }

    public function isVoid(): bool
    {
        return false;
    }

    public function asPrimitiveType(): PrimitiveType
    {
        return null;
    }

    public function asVectorType(): VectorType
    {
        return null;
    }

    public function asMapType(): MapType
    {
        return null;
    }

    public function asStructType(): StructType
    {
        return null;
    }
}
