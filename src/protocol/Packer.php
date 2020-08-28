<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use InvalidArgumentException;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\reflection\ReflectionType;
use RuntimeException;
use TUPAPI;
use Webmozart\Assert\Assert;
use wenbinye\tars\protocol\type\MapType;
use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\protocol\type\StructMapEntry;
use wenbinye\tars\protocol\type\Type;

class Packer implements PackerInterface, TypeParserInterface, TypeConverterInterface
{
    /**
     * @var TypeParserInterface
     */
    private $parser;

    /**
     * @var TypeConverterInterface
     */
    private $converter;

    public function __construct(AnnotationReaderInterface $annotationReader, bool $ignoreEmptyString = true)
    {
        self::check();
        $this->parser = new TypeParser();
        $this->converter = new TypeConverter($annotationReader, $this->parser, $ignoreEmptyString);
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

    public static function toPayload(string $name, string $payload): string
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

        if ($type->isEnum()) {
            return $type->asEnumType()->pack($name, $data, $version);
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

        if ($type->isEnum()) {
            return $type->asEnumType()->unpack($name, $payload, $version);
        }

        if ($type->isVector()) {
            $tarsType = $this->getTarsType($type);
            $data = TUPAPI::getVector($name, $tarsType, $payload, false, $version);

            return $this->convert($data, $type);
        }

        if ($type->isMap()) {
            $tarsType = $this->getTarsType($type);
            $data = TUPAPI::getMap($name, $tarsType, $payload, false, $version);

            return $this->convert($data, $type);
        }

        if ($type->isStruct()) {
            $tarsType = $this->getTarsType($type);
            $data = TUPAPI::getStruct($name, $tarsType, $payload, false, $version);

            return $this->convert($data, $type);
        }
        throw new InvalidArgumentException('unknown type to unpack: '.get_class($type));
    }

    private function toTarsType($data, Type $type)
    {
        if ($type->isPrimitive()) {
            return $data;
        }
        if ($type->isEnum()) {
            return $type->asEnumType()->getEnumValue($data);
        }
        if ($type->isVector()) {
            $vector = $this->getTarsType($type);
            if (isset($data)) {
                Assert::isArray($data);
                foreach ($data as $item) {
                    $vector->pushBack($this->toTarsType($item, $type->asVectorType()->getSubType()));
                }
            }

            return $vector;
        }
        if ($type->isMap()) {
            /** @var MapType $type */
            $map = $this->getTarsType($type);
            if (isset($data)) {
                $mapType = $type->asMapType();
                if ($type->getKeyType()->isPrimitive()) {
                    Assert::isArray($data);
                    foreach ($data as $key => $value) {
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
            }

            return $map;
        }
        if ($type->isStruct()) {
            $struct = $this->getTarsType($type);
            if (isset($data)) {
                Assert::isInstanceOf($data, $type->asStructType()->getClassName());
                foreach ($struct->getFields() as $field) {
                    $struct->{$field['name']} = $this->toTarsType($data->{$field['name']} ?? null, $field['typeObj']);
                }
            }

            return $struct;
        }
        throw new InvalidArgumentException('unknown type to convert: '.ReflectionType::describe($type));
    }

    /**
     * {@inheritdoc}
     */
    public function convert($data, Type $type)
    {
        return $this->converter->convert($data, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getTarsType(Type $type)
    {
        return $this->converter->getTarsType($type);
    }
}
