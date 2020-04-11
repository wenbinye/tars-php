<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class StructMapEntry implements \JsonSerializable
{
    /**
     * @var object
     */
    private $key;
    /**
     * @var object
     */
    private $value;

    /**
     * StructMapEntry constructor.
     *
     * @param object $key
     * @param object $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return object
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
