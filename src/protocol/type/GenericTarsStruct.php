<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\type;

class GenericTarsStruct extends \TARS_Struct
{
    /**
     * @var array
     */
    private $_fields;

    /**
     * GenericTarsStruct constructor.
     */
    public function __construct(string $className, array $fields)
    {
        parent::__construct($className, $fields);
        foreach ($fields as $field) {
            $this->{$field['name']} = null;
        }

        $this->_fields = $fields;
    }

    public function getFields(): array
    {
        return $this->_fields;
    }
}
