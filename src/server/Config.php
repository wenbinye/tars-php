<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use wenbinye\tars\server\annotation\ConfigItem;
use wenbinye\tars\server\exception\ConfigException;
use wenbinye\tars\support\CaseFormat;
use wenbinye\tars\support\Type;

class Config extends \ArrayIterator
{
    private static $INSTANCE;

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        return self::$INSTANCE;
    }

    /**
     * @param mixed $INSTANCE
     */
    public static function setInstance(Config $instance): void
    {
        self::$INSTANCE = $instance;
    }

    public function getInt($key): int
    {
        return (int) $this[$key];
    }

    public function getBool($key): bool
    {
        return (bool) $this[$key];
    }

    public function __get($name)
    {
        return $this[$name] ?? null;
    }

    public function __set($name, $value)
    {
        throw new \BadMethodCallException('Cannot modify config');
    }

    public function __isset($name)
    {
        return isset($this[$name]);
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function parseFile(string $fileName): Config
    {
        $content = file_get_contents($fileName);
        if (false === $content) {
            throw new ConfigException("cannot read config file '{$fileName}'");
        }

        return static::parse($content);
    }

    public static function parse(string $content): Config
    {
        $stack = [];
        $current = $config = new static();
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
                    $current = $current[$matches[2]] = new static();
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

        return $config;
    }

    public function updateTo($properties, \Doctrine\Common\Annotations\Reader $reader): void
    {
        $reflectionClass = new \ReflectionClass($properties);
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $getter = 'get'.$property->getName();
            if (!$reflectionClass->hasMethod($getter)) {
                continue;
            }
            /** @var ConfigItem $configItem */
            $configItem = $reader->getPropertyAnnotation($property, ConfigItem::class);
            if (!$configItem) {
                continue;
            }
            $value = null;
            foreach ([$configItem->name,
                         $property->name,
                         strtolower($property->name),
                         CaseFormat::snakeCase($property->name, '-'),
                         str_replace('ServantName', '', $property->name),
                     ] as $candidate) {
                if (isset($candidate, $this[$candidate])) {
                    $value = $this[$candidate];
                    break;
                }
            }
            if (!isset($value)) {
                continue;
            }
            $stringSetter = sprintf('set%sFromString', $property->getName());
            if ($reflectionClass->hasMethod($stringSetter)) {
                if ($configItem->factory) {
                    trigger_error(sprintf("Property '%s' of '%s' setter '%s' override factory method", get_class($properties), $property->getName(), $stringSetter));
                }
                $reflectionClass->getMethod($stringSetter)->invoke($properties, $value);
            } else {
                if ($configItem->factory) {
                    if (false !== strpos($configItem->factory, '::')) {
                        $value = call_user_func(explode('::', 2), $value);
                    } else {
                        $value = call_user_func([(string) $reflectionClass->getMethod($getter)->getReturnType(), $configItem->factory], $value);
                    }
                } else {
                    $type = $reflectionClass->getMethod($getter)->getReturnType();
                    $value = Type::fromString($type, (string) $value);
                }
                $reflectionClass->getMethod('set'.$property->getName())->invoke($properties, $value);
            }
        }
    }
}
