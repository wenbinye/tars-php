<?php

declare(strict_types=1);

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

    public function isEnum(): bool
    {
        return false;
    }

    public function isVoid(): bool
    {
        return false;
    }

    public function asPrimitiveType(): PrimitiveType
    {
        throw new \BadFunctionCallException('Cannot convert to primitive type');
    }

    public function asVectorType(): VectorType
    {
        throw new \BadFunctionCallException('Cannot convert to vector type');
    }

    public function asMapType(): MapType
    {
        throw new \BadFunctionCallException('Cannot convert to map type');
    }

    public function asEnumType(): EnumType
    {
        throw new \BadFunctionCallException('Cannot convert to enum type');
    }

    public function asStructType(): StructType
    {
        throw new \BadFunctionCallException('Cannot convert to struct type');
    }
}
