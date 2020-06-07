<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

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

    public function getEnumValue($enumObj): ?int
    {
        return $enumObj ? $enumObj->value : null;
    }

    public function createEnum($value)
    {
        return call_user_func([$this->className, 'fromValue'], $value);
    }

    public function asTarsType(): int
    {
        return \TARS::UINT8;
    }

    public function pack($name, $data, int $version)
    {
        return \TUPAPI::putUInt8($name, $data->value, $version);
    }

    public function unpack($name, string &$payload, int $version)
    {
        return $this->createEnum(\TUPAPI::getUInt8($name, $payload, false, $version));
    }
}
