<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReaderInterface;
use wenbinye\tars\protocol\annotation\TarsProperty;
use wenbinye\tars\protocol\exception\SyntaxErrorException;
use wenbinye\tars\protocol\type\GenericTarsStruct;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\protocol\type\Type;

class TypeConverter implements TypeConverterInterface
{
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var TypeParserInterface
     */
    private $typeParser;

    /**
     * @var array
     */
    private static $STRUCT_FIELDS = [];

    /**
     * TypeConverter constructor.
     */
    public function __construct(AnnotationReaderInterface $annotationReader, TypeParserInterface $typeParser)
    {
        $this->annotationReader = $annotationReader;
        $this->typeParser = $typeParser;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($data, Type $type)
    {
        if ($type->isPrimitive()) {
            return $data;
        }
        if ($type->isEnum()) {
            return $type->asEnumType()->createEnum($data);
        }
        if ($type->isVector()) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->convert($item, $type->asVectorType()->getSubType());
            }

            return $result;
        }
        if ($type->isMap()) {
            $mapType = $type->asMapType();
            if ($mapType->getKeyType()->isStruct()) {
                $result = new StructMap();
                foreach ($data as $entry) {
                    $result->put($this->convert($entry['key'], $mapType->getKeyType()),
                        $this->convert($entry['value'], $mapType->getValueType()));
                }
            } else {
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $this->convert($value, $mapType->getValueType());
                }
            }

            return $result;
        }
        if ($type->isStruct()) {
            $className = $type->asStructType()->getClassName();
            $obj = new $className();
            $struct = $this->getTarsType($type);
            foreach ($struct->getFields() as $field) {
                if (isset($data[$field['name']])) {
                    $obj->{$field['name']} = $this->convert($data[$field['name']], $field['typeObj']);
                }
            }

            return $obj;
        }
        throw new \InvalidArgumentException('unknown type to convert: '.get_class($type));
    }

    public function getTarsType(Type $type)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->asTarsType();
        }
        if ($type->isEnum()) {
            return $type->asEnumType()->asTarsType();
        }

        if ($type->isVector()) {
            return new \TARS_Vector($this->getTarsType($type->asVectorType()->getSubType()));
        }

        if ($type->isMap()) {
            $mapType = $type->asMapType();

            /* @noinspection PhpMethodParametersCountMismatchInspection */
            return new \TARS_Map($this->getTarsType($mapType->getKeyType()),
                $this->getTarsType($mapType->getValueType()), $mapType->getKeyType()->isStruct());
        }

        if ($type->isStruct()) {
            return $this->createTarsStructVar($type->asStructType()->getClassName());
        }
        throw new \InvalidArgumentException('unknown type');
    }

    /**
     * @throws SyntaxErrorException
     */
    private function createTarsStructVar(string $className): GenericTarsStruct
    {
        if (!isset(self::$STRUCT_FIELDS[$className])) {
            try {
                $reflectionClass = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                throw new SyntaxErrorException("Class not found '${className}'");
            }
            $namespace = $reflectionClass->getNamespaceName();
            $fields = [];
            foreach ($reflectionClass->getProperties() as $property) {
                /** @var TarsProperty $annotation */
                $annotation = $this->annotationReader->getPropertyAnnotation($property, TarsProperty::class);
                if ($annotation) {
                    $type = $this->typeParser->parse($annotation->type, $namespace);
                    $fields[$annotation->order] = [
                        'name' => $property->getName(),
                        'order' => $annotation->order,
                        'required' => $annotation->required,
                        'type' => $type->asTarsType(),
                        'typeObj' => $type,
                    ];
                }
            }
            self::$STRUCT_FIELDS[$className] = $fields;
        }

        $struct = new GenericTarsStruct($className, self::$STRUCT_FIELDS[$className]);
        foreach ($struct->getFields() as $field) {
            /** @var Type $fieldType */
            $fieldType = $field['typeObj'];
            if (!$fieldType->isPrimitive() && !$fieldType->isEnum()) {
                $struct->{$field['name']} = $this->getTarsType($fieldType);
            }
        }

        return $struct;
    }
}
