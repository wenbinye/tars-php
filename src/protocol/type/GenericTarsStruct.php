<?php

namespace wenbinye\tars\protocol\type;

class GenericTarsStruct extends \TARS_Struct
{
    private $_fields;

    /**
     * GenericTarsStruct constructor.
     */
    public function __construct(string $className, array $fields)
    {
        parent::__construct($className, $fields);

        $this->_fields = $fields;
    }

    public function getFields(): array
    {
        return $this->_fields;
    }
}
