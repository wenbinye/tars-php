<?php

namespace wenbinye\tars\protocol;

class VoidType extends AbstractType
{
    public function isVoid(): bool
    {
        return true;
    }
}
