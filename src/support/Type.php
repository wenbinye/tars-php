<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

class Type
{
    private static $FILTERS = [
    ];

    /**
     * @param string|\ReflectionType $type
     *
     * @return bool|int|string
     */
    public static function fromString($type, string $value)
    {
        $typeName = (string) $type;

        if ('int' === $typeName) {
            return (int) $value;
        } elseif ('bool' === $typeName) {
            return in_array(strtolower($value), ['1', 'true', 'on'], true);
        } else {
            return $value;
        }
        // throw new \InvalidArgumentException("Cannot cast $type from string");
    }
}
