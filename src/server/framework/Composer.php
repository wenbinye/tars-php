<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

class Composer
{
    public static function getJson(string $file = null): array
    {
        if (!$file) {
            $file = self::detect();
        }
        if (!is_readable($file)) {
            throw new \InvalidArgumentException('Cannot read composer.json');
        }
        $json = json_decode(file_get_contents($file), true);
        if (empty($json)) {
            throw new \InvalidArgumentException("invalid composer.json read from $file");
        }

        return $json;
    }

    public static function detect(): string
    {
        $dir = getcwd();
        while (!file_exists($dir.'/composer.json')) {
            $parentDir = dirname($dir);
            if ($parentDir === $dir) {
                throw new \InvalidArgumentException('Cannot detect project path, is there composer.json in current directory?');
            }
            $dir = $parentDir;
        }

        return $dir.'/composer.json';
    }
}
