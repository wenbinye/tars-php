<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\helper\Properties;
use wenbinye\tars\server\exception\ConfigException;

class Config
{
    /**
     * @var Properties
     */
    private static $INSTANCE;

    /**
     * @return mixed
     */
    public static function getInstance(): Properties
    {
        return self::$INSTANCE;
    }

    /**
     * @param mixed $INSTANCE
     */
    private static function setInstance(Properties $instance): void
    {
        self::$INSTANCE = $instance;
    }

    public static function parseFile(string $fileName): void
    {
        $content = file_get_contents($fileName);
        if (false === $content) {
            throw new ConfigException("cannot read config file '{$fileName}'");
        }

        static::parse($content);
    }

    public static function parse(string $content): void
    {
        $stack = [];
        $current = $config = Properties::create();
        foreach (explode("\n", $content) as $lineNum => $line) {
            $line = trim($line);
            if (empty($line) || 0 === strpos($line, '#')) {
                continue;
            }
            if (preg_match("/<(\/?)(\S+)>/", $line, $matches)) {
                if ($matches[1]) {
                    if (empty($stack)) {
                        throw new ConfigException("Unexpect close tag '{$line}' at line {$lineNum}");
                    }
                    $current = array_pop($stack);
                } else {
                    $stack[] = $current;
                    $current = $current[$matches[2]] = Properties::create();
                }
            } else {
                $parts = array_map('trim', explode('=', $line, 2));
                if (1 === count($parts)) {
                    $current[$parts[0]] = true;
                } else {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }
        static::setInstance($config);
    }
}
