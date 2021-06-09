<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

interface PackerInterface
{
    /**
     * Packs php data type to binary data.
     *
     * @param Type   $type
     * @param string $name
     * @param mixed  $data
     * @param int    $version
     *
     * @return string
     */
    public function pack(Type $type, string $name, $data, int $version = Version::TUP): string;

    /**
     * Unpacks binary data to php data type.
     *
     * @param Type   $type
     * @param string $name
     * @param string $payload
     * @param int    $version
     *
     * @return mixed
     */
    public function unpack(Type $type, string $name, string &$payload, int $version = Version::TUP);
}
