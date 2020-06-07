<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\fixtures;

class EnumStructOld extends \TARS_Struct
{
    const TYPE = 0;

    public $type;

    protected static $fields = [
        self::TYPE => [
            'name' => 'type',
            'required' => true,
            'type' => \TARS::UINT8,
            ],
    ];

    public function __construct()
    {
        parent::__construct('App_Server_Servant.EnumStruct', self::$fields);
    }
}
