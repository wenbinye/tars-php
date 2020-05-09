<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

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

    public static function detect(string $basePath = null): string
    {
        if (!$basePath) {
            $basePath = getcwd();
        }
        while (!file_exists($basePath.'/composer.json')) {
            $parentDir = dirname($basePath);
            if ($parentDir === $basePath) {
                throw new \InvalidArgumentException('Cannot detect project path, is there composer.json in current directory?');
            }
            $basePath = $parentDir;
        }

        return $basePath.'/composer.json';
    }
}
