<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

interface PackerInterface
{
    public function pack(Type $type, $name, $data, int $version): string;

    public function unpack(Type $type, $name, string &$payload, int $version);
}
