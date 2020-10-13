<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

interface Type
{
    public function isPrimitive(): bool;

    public function isStruct(): bool;

    public function isVector(): bool;

    public function isMap(): bool;

    public function isEnum(): bool;

    public function isVoid(): bool;

    public function asPrimitiveType(): PrimitiveType;

    public function asVectorType(): VectorType;

    public function asMapType(): MapType;

    public function asEnumType(): EnumType;

    public function asStructType(): StructType;

    public function asTarsType(): int;

    public function __toString(): string;
}
