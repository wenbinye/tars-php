<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use InvalidArgumentException;
use kuiper\annotations\AnnotationReaderInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TARS_Map;
use TARS_Vector;
use TUPAPI;
use Webmozart\Assert\Assert;
use wenbinye\tars\protocol\annotation\TarsProperty;
use wenbinye\tars\protocol\exception\SyntaxErrorException;
use wenbinye\tars\protocol\type\GenericTarsStruct;
use wenbinye\tars\protocol\type\MapType;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\protocol\type\StructMapEntry;
use wenbinye\tars\protocol\type\Type;

class Packer implements PackerInterface, TypeParserInterface
{
    /**
     * @var array
     */
    private static $STRUCT_FIELDS;

    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var TypeParserInterface
     */
    private $parser;

    /**
     * Packer constructor.
     *
     * @param TypeParserInterface $parser
     */
    public function __construct(AnnotationReaderInterface $annotationReader)
    {
        self::check();
        $this->annotationReader = $annotationReader;
        $this->parser = new TypeParser();
    }

    public static function check(): void
    {
        if (!extension_loaded('phptars')) {
            throw new RuntimeException('extension phptars should be enabled');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $type, string $namespace = ''): Type
    {
        return $this->parser->parse($type, $namespace);
    }

    public static function asPayload(string $name, string $payload): string
    {
        $requestBuf = \TUPAPI::encode(Version::TUP, 1, '',
            '', 0, 0, 0,
            [], [], [$name => $payload]);
        $decodeRet = \TUPAPI::decode($requestBuf);

        return $decodeRet['sBuffer'];
    }

    /**
     * {@inheritdoc}
     */
    public function pack(Type $type, $name, $data, int $version = Version::TUP): string
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->pack($name, $data, $version);
        }

        if ($type->isVector()) {
            return TUPAPI::putVector($name, $this->toTarsType($data, $type), $version);
        }

        if ($type->isMap()) {
            return TUPAPI::putMap($name, $this->toTarsType($data, $type), $version);
        }

        if ($type->isStruct()) {
            return TUPAPI::putStruct($name, $this->toTarsType($data, $type), $version);
        }
        throw new InvalidArgumentException('unknown type to pack: '.get_class($type));
    }

    /**
     * {@inheritdoc}
     */
    public function unpack(Type $type, $name, string &$payload, int $version = Version::TUP)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->unpack($name, $payload, $version);
        }

        if ($type->isVector()) {
            $tarsType = $this->createTarsVar($type);
            $data = TUPAPI::getVector($name, $tarsType, $payload, false, $version);

            return $this->toPhpType($data, $type);
        }

        if ($type->isMap()) {
            $tarsType = $this->createTarsVar($type);
            $data = TUPAPI::getMap($name, $tarsType, $payload, false, $version);

            return $this->toPhpType($data, $type);
        }

        if ($type->isStruct()) {
            $tarsType = $this->createTarsVar($type);
            $data = TUPAPI::getStruct($name, $tarsType, $payload, false, $version);

            return $this->toPhpType($data, $type);
        }
        throw new InvalidArgumentException('unknown type to unpack: '.get_class($type));
    }

    private function toTarsType($data, Type $type)
    {
        if ($type->isPrimitive()) {
            return $data;
        }
        if (!isset($data)) {
            $data = [];
        }
        if ($type->isVector()) {
            Assert::isArray($data);
            $vector = $this->createTarsVar($type);

            foreach ($data as $item) {
                $vector->pushBack($this->toTarsType($item, $type->asVectorType()->getSubType()));
            }

            return $vector;
        }
        if ($type->isMap()) {
            /** @var MapType $type */
            $map = $this->createTarsVar($type);
            $mapType = $type->asMapType();
            if ($type->getKeyType()->isPrimitive()) {
                Assert::isArray($data);
                foreach ($data as $key => $value) {
                    /* @noinspection PhpIllegalArrayKeyTypeInspection */
                    $map->pushBack([
                        $this->toTarsType($key, $mapType->getKeyType()) => $this->toTarsType($value, $mapType->getValueType()),
                    ]);
                }
            } else {
                Assert::isInstanceOf($data, StructMap::class);
                /** @var StructMapEntry $entry */
                foreach ($data as $entry) {
                    $map->pushBack([
                        'key' => $this->toTarsType($entry->getKey(), $mapType->getKeyType()),
                        'value' => $this->toTarsType($entry->getValue(), $mapType->getValueType()),
                    ]);
                }
            }

            return $map;
        }
        if ($type->isStruct()) {
            $struct = $this->createTarsVar($type);
            foreach ($struct->getFields() as $field) {
                $struct->{$field['name']} = $this->toTarsType($data->{$field['name']} ?? null, $field['typeObj']);
            }

            return $struct;
        }
        throw new InvalidArgumentException('unknown type to convert: '.get_class($type));
    }

    private function toPhpType($data, Type $type)
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
            $struct = $this->createTarsVar($type);
            foreach ($struct->getFields() as $field) {
                if (isset($data[$field['name']])) {
                    $obj->{$field['name']} = $this->toPhpType($data[$field['name']], $field['typeObj']);
                }
            }

            return $obj;
        }
        throw new InvalidArgumentException('unknown type to convert: '.get_class($type));
    }

    private function createTarsVar(Type $type)
    {
        if ($type->isPrimitive()) {
            return $type->asPrimitiveType()->asTarsType();
        }

        if ($type->isVector()) {
            return new TARS_Vector($this->createTarsVar($type->asVectorType()->getSubType()));
        }

        if ($type->isMap()) {
            $mapType = $type->asMapType();

            /* @noinspection PhpMethodParametersCountMismatchInspection */
            return new TARS_Map($this->createTarsVar($mapType->getKeyType()),
                $this->createTarsVar($mapType->getValueType()), $mapType->getKeyType()->isStruct());
        }

        if ($type->isStruct()) {
            return $this->createTarsStructVar($type->asStructType()->getClassName());
        }
        throw new InvalidArgumentException('unknown type');
    }

    /**
     * @throws SyntaxErrorException
     */
    private function createTarsStructVar(string $className): GenericTarsStruct
    {
        if (!isset(self::$STRUCT_FIELDS[$className])) {
            try {
                $reflectionClass = new ReflectionClass($className);
            } catch (ReflectionException $e) {
                throw new SyntaxErrorException("Class not found '${className}'");
            }
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
                $struct->{$field['name']} = $this->createTarsVar($fieldType);
            }
        }

        return $struct;
    }
}
