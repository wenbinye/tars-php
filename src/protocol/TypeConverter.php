<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReaderInterface;
use Webmozart\Assert\Assert;
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
     * @var bool
     */
    private $ignoreEmptyString;

    /**
     * @var array
     */
    private static $STRUCT_FIELDS = [];

    /**
     * TypeConverter constructor.
     */
    public function __construct(AnnotationReaderInterface $annotationReader, TypeParserInterface $typeParser, bool $ignoreEmptyString = true)
    {
        $this->annotationReader = $annotationReader;
        $this->typeParser = $typeParser;
        $this->ignoreEmptyString = $ignoreEmptyString;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($data, Type $type)
    {
        if ($type->isPrimitive()) {
            Assert::true(is_scalar($data), 'Expected scalar, got '.gettype($data));

            return $data;
        }
        if ($type->isEnum()) {
            return $type->asEnumType()->createEnum($data);
        }
        if ($type->isVector()) {
            Assert::isArray($data);
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->convert($item, $type->asVectorType()->getSubType());
            }

            return $result;
        }
        if ($type->isMap()) {
            Assert::isArray($data);
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
            Assert::isArray($data);
            $className = $type->asStructType()->getClassName();
            $obj = new $className();
            $struct = $this->getTarsType($type);
            foreach ($struct->getFields() as $field) {
                if (isset($data[$field['name']])
                && $this->acceptEmptyString($data[$field['name']], $field)) {
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
                /** @var TarsProperty|null $annotation */
                $annotation = $this->annotationReader->getPropertyAnnotation($property, TarsProperty::class);
                if (null !== $annotation) {
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

    /**
     * @param mixed $value
     * @param array $field
     *
     * @return bool
     */
    private function acceptEmptyString($value, array $field): bool
    {
        if ($this->ignoreEmptyString && \TARS::STRING === $field['type'] && '' === $value) {
            return false;
        }

        return true;
    }
}
