<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class PrimitiveType extends AbstractType
{
    /**
     * @var int
     */
    private $tarsType;

    /**
     * @var string[]
     */
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

    /**
     * @param string|int $name
     * @param mixed|null $data
     * @param int        $version
     *
     * @return string
     */
    public function pack($name, $data, int $version)
    {
        if (isset($data) && !is_scalar($data)) {
            throw new \InvalidArgumentException(sprintf('Cannot pack %s to %s', gettype($data), (string) $this));
        }
        $method = 'put'.self::$PACK_METHODS[$this->tarsType];

        return \TUPAPI::{$method}((string) $name, $data, $version);
    }

    /**
     * @param string|int $name
     * @param string     $payload
     * @param int        $version
     *
     * @return mixed
     */
    public function unpack($name, string &$payload, int $version)
    {
        $method = 'get'.self::$PACK_METHODS[$this->tarsType];

        return \TUPAPI::{$method}((string) $name, $payload, false, $version);
    }

    public function __toString(): string
    {
        return self::$TYPES[$this->tarsType];
    }

    /**
     * @param mixed $data
     *
     * @return bool|float|int|string|null
     */
    public function getValue($data)
    {
        if (null === $data) {
            return null;
        }
        switch ($this->tarsType) {
            case \TARS::BOOL:
                return (bool) $data;
            case \TARS::UINT8:
            case \TARS::SHORT:
            case \TARS::UINT16:
            case \TARS::INT32:
            case \TARS::UINT32:
            case \TARS::INT64:
                return (int) $data;
            case \TARS::FLOAT:
            case \TARS::DOUBLE:
                return (float) $data;
            case \TARS::CHAR:
            case \TARS::STRING:
                return (string) $data;
            default:
                throw new \InvalidArgumentException('unknown tars type');
        }
    }
}
