<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class PrimitiveType extends AbstractType
{
    /**
     * @var int
     */
    private $tarsType;

    private static $PACK_METHODS = [
        \TARS::BOOL => 'Bool',
        \TARS::CHAR => 'Char',
        \TARS::DOUBLE => 'Double',
        \TARS::FLOAT => 'Float',
        \TARS::SHORT => 'Short',
        \TARS::INT32 => 'Int32',
        \TARS::INT64 => 'Int64',
        \TARS::UINT8 => 'UInt8',
        \TARS::UINT16 => 'UInt16',
        \TARS::UINT32 => 'UInt32',
        \TARS::STRING => 'String',
    ];

    /**
     * @var string[]
     */
    private static $TYPES = [
        \TARS::BOOL => 'bool',
        \TARS::CHAR => 'char',
        \TARS::UINT8 => 'unsigned char',
        \TARS::SHORT => 'short',
        \TARS::UINT16 => 'unsigned short',
        \TARS::INT32 => 'int',
        \TARS::UINT32 => 'unsigned int',
        \TARS::INT64 => 'long',
        \TARS::FLOAT => 'float',
        \TARS::DOUBLE => 'double',
        \TARS::STRING => 'string',
    ];

    /**
     * PrimitiveType constructor.
     */
    public function __construct(int $primitiveType)
    {
        if (!isset(self::$TYPES[$primitiveType])) {
            throw new \InvalidArgumentException("unknown primitive tars type $primitiveType");
        }
        $this->tarsType = $primitiveType;
    }

    public function asTarsType(): int
    {
        return $this->tarsType;
    }

    public function asPrimitiveType(): PrimitiveType
    {
        return $this;
    }

    public function isPrimitive(): bool
    {
        return true;
    }

    public function pack($name, $data)
    {
        $method = 'put'.self::$PACK_METHODS[$this->tarsType];

        return \TUPAPI::{$method}($name, $data);
    }

    public function unpack($name, string $payload)
    {
        $method = 'get'.self::$PACK_METHODS[$this->tarsType];

        return \TUPAPI::{$method}($name, $payload);
    }

    public function __toString()
    {
        return self::$TYPES[$this->tarsType];
    }
}
