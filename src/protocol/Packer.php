<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

class Packer implements PackerInterface
{
    /**
     * @var TarsTypeFactory
     */
    private $tarsTypeFactory;

    /**
     * @var TypeConverter
     */
    private $typeConverter;

    /**
     * Packer constructor.
     */
    public function __construct(TarsTypeFactory $tarsTypeFactory)
    {
        $this->tarsTypeFactory = $tarsTypeFactory;
        $this->typeConverter = new TypeConverter($tarsTypeFactory);
    }

    public function pack(Type $type, $name, $data, int $version): string
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->pack($name, $data, $version);
        } elseif ($type->isVector()) {
            return \TUPAPI::putVector($name, $this->typeConverter->toTarsType($data, $type), $version);
        } elseif ($type->isMap()) {
            return \TUPAPI::putMap($name, $this->typeConverter->toTarsType($data, $type), $version);
        } elseif ($type->isStruct()) {
            return \TUPAPI::putStruct($name, $this->typeConverter->toTarsType($data, $type), $version);
        }
        throw new \InvalidArgumentException('unknown type to pack: '.get_class($type));
    }

    public function unpack(Type $type, $name, string &$payload, int $version)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->unpack($name, $payload, $version);
        } elseif ($type->isVector()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getVector($name, $tarsType, $payload, false, $version);

            return $this->typeConverter->toPhpType($data, $type);
        } elseif ($type->isMap()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getMap($name, $tarsType, $payload, false, $version);

            return $this->typeConverter->toPhpType($data, $type);
        } elseif ($type->isStruct()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getStruct($name, $tarsType, $payload, false, $version);

            return $this->typeConverter->toPhpType($data, $type);
        }
        throw new \InvalidArgumentException('unknown type to unpack: '.get_class($type));
    }
}
