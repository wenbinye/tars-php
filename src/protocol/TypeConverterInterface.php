<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

interface TypeConverterInterface
{
    /**
     * Converts raw data to php type.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function convert($data, Type $type);

    /**
     * @return mixed
     */
    public function getTarsType(Type $type);
}
