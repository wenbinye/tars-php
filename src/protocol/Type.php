<?php

namespace wenbinye\tars\protocol;

interface Type
{
    public function isPrimitive(): bool;

    public function isStruct(): bool;

    public function isVector(): bool;

    public function isMap(): bool;

    public function isVoid(): bool;

    public function asPrimitiveType(): PrimitiveType;

    public function asVectorType(): VectorType;

    public function asMapType(): MapType;

    public function asStructType(): StructType;
}
