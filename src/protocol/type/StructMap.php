<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class StructMap extends \ArrayIterator
{
    public function put($key, $value): void
    {
        $this->append(new StructMapEntry($key, $value));
    }
}
