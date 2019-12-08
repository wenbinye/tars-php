<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\protocol\annotation\TarsProperty;
use wenbinye\tars\protocol\type\GenericTarsStruct;
use wenbinye\tars\protocol\type\Type;

class TarsTypeFactory
{
    /**
     * @var Reader
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
    public function __construct(Reader $annotationReader)
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
            return new \TARS_Map($this->create($type->asMapType()->getKeyType()),
                $this->create($type->asMapType()->getValueType()));
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
