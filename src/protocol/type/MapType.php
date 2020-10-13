<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class MapType extends AbstractType
{
    /**
     * @var Type
     */
    private $keyType;
    /**
     * @var Type
     */
    private $valueType;

    /**
     * MapType constructor.
     */
    public function __construct(Type $keyType, Type $valueType)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;
    }

    public function getKeyType(): Type
    {
        return $this->keyType;
    }

    public function getValueType(): Type
    {
        return $this->valueType;
    }

    public function isMap(): bool
    {
        return true;
    }

    public function asMapType(): MapType
    {
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('map<%s, %s>', (string) $this->keyType, (string) $this->valueType);
    }

    public function asTarsType(): int
    {
        return \TARS::MAP;
    }
}
