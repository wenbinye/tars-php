<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

interface PackerInterface
{
    public function pack($name, $data, Type $type): string;

    public function unpack($name, string $payload, Type $type);
}
