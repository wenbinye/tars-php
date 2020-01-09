<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReaderInterface;
use wenbinye\tars\protocol\annotation\TarsProperty;
use wenbinye\tars\protocol\type\GenericTarsStruct;
use wenbinye\tars\protocol\type\Type;

class TarsTypeFactory
{
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var TypeParser
     */
    private $parser;

    /**
     * @var array
     */
    private static $STRUCT_FIELDS;

    /**
     * TarsTypeFactory constructor.
     */
    public function __construct(AnnotationReaderInterface $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->parser = new TypeParser();
    }

    public function create(Type $type)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->asTarsType();
        }

        if ($type->isVector()) {
            return new \TARS_Vector($this->create($type->asVectorType()->getSubType()));
        }

        if ($type->isMap()) {
            $mapType = $type->asMapType();

            return new \TARS_Map($this->create($mapType->getKeyType()),
                $this->create($mapType->getValueType()), $mapType->getKeyType()->isStruct());
        }

        if ($type->isStruct()) {
            return $this->getStructTarsType($type->asStructType()->getClassName());
        }
        throw new \InvalidArgumentException('unknown type');
    }

    private function getStructTarsType(string $className): GenericTarsStruct
    {
        if (!isset(self::$STRUCT_FIELDS[$className])) {
            $reflectionClass = new \ReflectionClass($className);
            $namespace = $reflectionClass->getNamespaceName();
            $fields = [];
            foreach ($reflectionClass->getProperties() as $property) {
                /** @var TarsProperty $annotation */
                $annotation = $this->annotationReader->getPropertyAnnotation($property, TarsProperty::class);
                if ($annotation) {
                    $type = $this->parser->parse($annotation->type, $namespace);
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
            if (!$fieldType->isPrimitive()) {
                $struct->{$field['name']} = $this->create($fieldType);
            }
        }

        return $struct;
    }
}
