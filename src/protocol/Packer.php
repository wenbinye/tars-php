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

    public function pack($name, $data, Type $type): string
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->pack($name, $data);
        } elseif ($type->isVector()) {
            return \TUPAPI::putVector($name, $this->typeConverter->toTarsType($data, $type));
        } elseif ($type->isMap()) {
            return \TUPAPI::putMap($name, $this->typeConverter->toTarsType($data, $type));
        } elseif ($type->isStruct()) {
            return \TUPAPI::putStruct($name, $this->typeConverter->toTarsType($data, $type));
        }
        throw new \InvalidArgumentException('unknown type to pack: '.get_class($type));
    }

    public function unpack($name, string $payload, Type $type)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->unpack($name, $payload);
        } elseif ($type->isVector()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getVector($name, $tarsType, $payload);

            return $this->typeConverter->toPhpType($data, $type);
        } elseif ($type->isMap()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getMap($name, $tarsType, $payload);

            return $this->typeConverter->toPhpType($data, $type);
        } elseif ($type->isStruct()) {
            $tarsType = $this->tarsTypeFactory->create($type);
            $data = \TUPAPI::getStruct($name, $tarsType, $payload);

            return $this->typeConverter->toPhpType($data, $type);
        }
        throw new \InvalidArgumentException('unknown type to unpack: '.get_class($type));
    }
}
