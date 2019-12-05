<?php

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\type\Type;

class TypeConverter
{
    /**
     * @var TarsTypeFactory
     */
    private $tarsTypeFactory;

    /**
     * TypeConverter constructor.
     */
    public function __construct(TarsTypeFactory $tarsTypeFactory)
    {
        $this->tarsTypeFactory = $tarsTypeFactory;
    }

    public function toTarsType($data, Type $type)
    {
        if ($type->isPrimitive()) {
            return $data;
        }
        if ($type->isVector()) {
            $vector = $this->tarsTypeFactory->create($type);
            if (!is_array($data)) {
                throw new \InvalidArgumentException('expect array, got '.gettype($data));
            }
            foreach ($data as $item) {
                $vector->pushBack($this->toTarsType($item, $type->asVectorType()->getSubType()));
            }

            return $vector;
        }
        if ($type->isMap()) {
            $map = $this->tarsTypeFactory->create($type);
            foreach ($data as $key => $value) {
                $map->pushBack([$this->toTarsType($key, $type->asMapType()->getKeyType()) => $this->toTarsType($value, $type->asMapType()->getValueType())]);
            }

            return $map;
        }
        if ($type->isStruct()) {
            $struct = $this->tarsTypeFactory->create($type);
            foreach ($struct->getFields() as $field) {
                $struct->{$field['name']} = $this->toTarsType($data->{$field['name']}, $field['typeObj']);
            }

            return $struct;
        }
        throw new \InvalidArgumentException('unknown type to convert: '.get_class($type));
    }

    public function toPhpType($data, Type $type)
    {
        if ($type->isPrimitive()) {
            return $data;
        }
        if ($type->isVector()) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->toPhpType($item, $type->asVectorType()->getSubType());
            }

            return $result;
        }
        if ($type->isMap()) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = $this->toPhpType($value, $type->asMapType()->getValueType());
            }

            return $result;
        }
        if ($type->isStruct()) {
            $className = $type->asStructType()->getClassName();
            $obj = new $className();
            $struct = $this->tarsTypeFactory->create($type);
            foreach ($struct->getFields() as $field) {
                if (isset($data[$field['name']])) {
                    $obj->{$field['name']} = $this->toPhpType($data[$field['name']], $field['typeObj']);
                }
            }

            return $obj;
        }
        throw new \InvalidArgumentException('unknown type to convert: '.get_class($type));
    }
}
