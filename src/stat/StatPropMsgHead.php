<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class StatPropMsgHead
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $moduleName;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     *
     * @var string
     */
    public $ip;

    /**
     * @TarsProperty(order = 2, required = true, type = "string")
     *
     * @var string
     */
    public $propertyName;

    /**
     * @TarsProperty(order = 3, required = false, type = "string")
     *
     * @var string
     */
    public $setName;

    /**
     * @TarsProperty(order = 4, required = false, type = "string")
     *
     * @var string
     */
    public $setArea;

    /**
     * @TarsProperty(order = 5, required = false, type = "string")
     *
     * @var string
     */
    public $setID;

    /**
     * @TarsProperty(order = 6, required = false, type = "string")
     *
     * @var string
     */
    public $sContainer;

    /**
     * @TarsProperty(order = 7, required = false, type = "int")
     *
     * @var int
     */
    public $iPropertyVer = 1;
}
