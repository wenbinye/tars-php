<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

/**
 * enum class.
 *
 * @property string name
 * @property mixed value
 */
abstract class Enum implements \JsonSerializable
{
    /**
     * key = className
     * value = array which key is enum value.
     */
    private static $VALUES = [];

    /**
     * key = className
     * value = array which key is enum name.
     */
    private static $NAMES = [];

    /**
     * properties for enum instances.
     */
    protected static $PROPERTIES = [];

    /**
     * @var string name of enum
     */
    private $name;

    /**
     * @var mixed value of enum
     */
    private $value;

    /**
     * Constructor.
     *
     * @param string $name
     * @param mixed  $value
     */
    private function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets name of enum instance.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Gets value of enum instance.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * default to string method.
     *
     * @return string name of enum
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Gets properties.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset(static::$PROPERTIES[$name])) {
            return static::$PROPERTIES[$name][$this->value] ?? null;
        }

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \InvalidArgumentException('Undefined property: '.get_class($this).'::$'.$name);
    }

    public function __isset($name)
    {
        return isset(static::$PROPERTIES[$name][$this->value])
            || isset($this->$name);
    }

    /**
     * Gets all enum values.
     */
    public static function values(): array
    {
        return array_keys(static::getValues());
    }

    /**
     * Gets all enum names.
     *
     * @return string[]
     */
    public static function names(): array
    {
        return array_keys(static::getNames());
    }

    /**
     * Gets all enums.
     *
     * @return static[]
     */
    public static function instances(): array
    {
        return array_values(self::getValues());
    }

    /**
     * Checks whether the enum value exists.
     *
     * @param mixed $value
     */
    public static function hasValue($value): bool
    {
        return array_key_exists($value, static::getValues());
    }

    /**
     * Gets the name for the enum value.
     *
     * @param mixed $value
     */
    public static function nameOf($value): string
    {
        $values = static::getValues();

        return isset($values[$value]) ? $values[$value]->name() : null;
    }

    /**
     * Checks whether the name of enum value exists.
     */
    public static function hasName(string $name): bool
    {
        return array_key_exists($name, static::getNames());
    }

    /**
     * Gets the enum value for the name.
     *
     * @return mixed value of
     */
    public static function valueOf(string $name)
    {
        $names = static::getNames();

        return $names[$name] ?? null;
    }

    /**
     * Gets the enum instance for the name.
     *
     * @param static $default
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromName(string $name, $default = null): Enum
    {
        $names = static::getNames();
        if (array_key_exists($name, $names)) {
            return self::fromValue($names[$name]);
        }
        if (null === $default) {
            throw new \InvalidArgumentException("No enum constant '$name' in class ".static::class);
        }

        return $default;
    }

    /**
     * Gets the enum instance for the value.
     *
     * @param mixed  $value
     * @param static $default
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromValue($value, $default = null): Enum
    {
        $values = static::getValues();
        if (array_key_exists($value, $values)) {
            return $values[$value];
        }
        if (null === $default) {
            throw new \InvalidArgumentException("No enum constant value '$value' class ".static::class);
        }

        return $default;
    }

    /**
     * Returns a value when called statically like so: MyEnum::SOME_VALUE() given SOME_VALUE is a class constant.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function __callStatic($name, $arguments)
    {
        if (static::hasName($name)) {
            return static::fromName($name);
        }

        throw new \BadMethodCallException("unknown method '$name'");
    }

    public function jsonSerialize()
    {
        return $this->name;
    }

    /**
     * @return static[]
     */
    protected static function getValues(): array
    {
        $class = static::class;
        if (!array_key_exists($class, self::$VALUES)) {
            try {
                $reflect = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \RuntimeException('unexpected reflection exception', $e);
            }
            $constants = $reflect->getConstants();
            self::$NAMES[$class] = $constants;
            $flip = [];
            foreach ($constants as $name => $val) {
                $flip[$val] = new $class($name, $val);
            }
            // Should not use `array_flip` here, error will be triggered if value is true or false
            // array_flip(): Can only flip STRING and INTEGER values! on line 1
            self::$VALUES[$class] = $flip;
        }

        return self::$VALUES[$class];
    }

    protected static function getNames()
    {
        $class = static::class;
        if (!isset(self::$NAMES[$class])) {
            static::getValues();
        }

        return self::$NAMES[$class];
    }
}
