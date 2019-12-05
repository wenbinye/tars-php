<?php

namespace wenbinye\tars\protocol\type;

class VoidType extends AbstractType
{
    public function isVoid(): bool
    {
        return true;
    }

    public function __toString()
    {
        return 'void';
    }

    public function asTarsType(): int
    {
        throw new \BadMethodCallException('cannot cast void to tars type');
    }
}
