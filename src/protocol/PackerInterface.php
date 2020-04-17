<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

interface PackerInterface
{
    /**
     * Packs php data type to binary data.
     *
     * @param string|int $name
     * @param mixed      $data
     */
    public function pack(Type $type, $name, $data, int $version = Version::TUP): string;

    /**
     * Unpacks binary data to php data type.
     *
     * @param string|int $name
     *
     * @return mixed
     */
    public function unpack(Type $type, $name, string &$payload, int $version = Version::TUP);
}
