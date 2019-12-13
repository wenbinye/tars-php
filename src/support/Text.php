<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

class Text
{
    /**
     * Converts strings to camel case style.
     *
     * <code>
     *    echo CaseFormat::camelCase('coco_bongo'); // CocoBongo
     *    echo CaseFormat::camelCase('co_co-bon_go', '-'); // Co_coBon_go
     *    echo CaseFormat::camelCase('co_co-bon_go', '_-'); // CoCoBonGo
     * </code>
     *
     * @param string $delimiter
     */
    public static function camelCase(string $str, string $delimiter = null): string
    {
        $sep = "\x00";
        $replace = null === $delimiter ? ['_'] : str_split($delimiter);

        return implode('', array_map('ucfirst', explode($sep, str_replace($replace, $sep, $str))));
    }

    /**
     * snake case strings which are camel case.
     *
     * <code>
     *    echo Text::uncamelize('CocoBongo'); // coco_bongo
     *    echo Text::uncamelize('CocoBongo', '-'); // coco-bongo
     * </code>
     *
     * @param string $delimiter
     */
    public static function snakeCase(string $str, string $delimiter = null): string
    {
        preg_match_all('/([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)/', $str, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match === strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode($delimiter ?? '_', $ret);
    }

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
