<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use Webmozart\Assert\Assert;
use wenbinye\tars\protocol\type\MapType;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\protocol\type\StructMapEntry;
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
        if (!isset($data)) {
            $data = [];
        }
        if ($type->isVector()) {
            Assert::isArray($data);
            $vector = $this->tarsTypeFactory->create($type);

            foreach ($data as $item) {
                $vector->pushBack($this->toTarsType($item, $type->asVectorType()->getSubType()));
            }

            return $vector;
        }
        if ($type->isMap()) {
            /** @var MapType $type */
            $map = $this->tarsTypeFactory->create($type);
            if ($type->getKeyType()->isPrimitive()) {
                Assert::isArray($data);
                foreach ($data as $key => $value) {
                    $map->pushBack([$this->toTarsType($key, $type->asMapType()->getKeyType()) => $this->toTarsType($value, $type->asMapType()->getValueType())]);
                }
            } else {
                Assert::isInstanceOf($data, StructMap::class);
                /** @var StructMapEntry $entry */
                foreach ($data as $entry) {
                    $map->pushBack([
                        'key' => $this->toTarsType($entry->getKey(), $type->asMapType()->getKeyType()),
                        'value' => $this->toTarsType($entry->getValue(), $type->asMapType()->getValueType()),
                    ]);
                }
            }

            return $map;
        }
        if ($type->isStruct()) {
            $struct = $this->tarsTypeFactory->create($type);
            foreach ($struct->getFields() as $field) {
                $struct->{$field['name']} = $this->toTarsType($data->{$field['name']} ?? null, $field['typeObj']);
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
            $mapType = $type->asMapType();
            if ($mapType->getKeyType()->isStruct()) {
                $result = new StructMap();
                foreach ($data as $entry) {
                    $result->put($this->toPhpType($entry['key'], $mapType->getKeyType()),
                        $this->toPhpType($entry['value'], $mapType->getValueType()));
                }
            } else {
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $this->toPhpType($value, $mapType->getValueType());
                }
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
