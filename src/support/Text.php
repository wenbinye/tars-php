<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

class Text
{
    public static function startsWith(string $haystack, string $needle, $ignoreCase = true): bool
    {
        if ('' === $needle) {
            return true;
        }

        return $ignoreCase ? 0 === strncasecmp($haystack, $needle, strlen($needle))
            : 0 === strncmp($haystack, $needle, strlen($needle));
    }

    public static function endsWith(string $haystack, string $needle, $ignoreCase = true): bool
    {
        if ('' === $needle) {
            return true;
        }
        $temp = strlen($haystack) - strlen($needle);
        if ($temp < 0) {
            return false;
        }

        return $ignoreCase ? false !== stripos($haystack, $needle, $temp)
            : false !== strpos($haystack, $needle, $temp);
    }
}
