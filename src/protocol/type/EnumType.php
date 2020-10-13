<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

use kuiper\helper\Enum;

class EnumType extends AbstractType
{
    /**
     * @var string
     */
    private $className;

    /**
     * StructType constructor.
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function asEnumType(): EnumType
    {
        return $this;
    }

    public function isEnum(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return $this->className;
    }

    /**
     * @param mixed $enumObj
     *
     * @return int|null
     */
    public function getEnumValue($enumObj): ?int
    {
        return is_object($enumObj) ? $enumObj->value : $enumObj;
    }

    /**
     * @param mixed $value
     *
     * @return Enum
     */
    public function createEnum($value)
    {
        return call_user_func([$this->className, 'fromValue'], $value);
    }

    public function asTarsType(): int
    {
        return \TARS::UINT8;
    }

    /**
     * @param string   $name
     * @param Enum|int $data
     * @param int      $version
     *
     * @return string
     */
    public function pack($name, $data, int $version): string
    {
        if ($data instanceof Enum) {
            return \TUPAPI::putUInt8($name, $data->value(), $version);
        } else {
            return \TUPAPI::putUInt8($name, $data, $version);
        }
    }

    /**
     * @param string $name
     * @param string $payload
     * @param int    $version
     *
     * @return Enum
     */
    public function unpack($name, string &$payload, int $version)
    {
        return $this->createEnum(\TUPAPI::getUInt8($name, $payload, false, $version));
    }
}
