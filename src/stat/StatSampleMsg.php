<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class StatSampleMsg
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $unid;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     *
     * @var string
     */
    public $masterName;

    /**
     * @TarsProperty(order = 2, required = true, type = "string")
     *
     * @var string
     */
    public $slaveName;

    /**
     * @TarsProperty(order = 3, required = true, type = "string")
     *
     * @var string
     */
    public $interfaceName;

    /**
     * @TarsProperty(order = 4, required = true, type = "string")
     *
     * @var string
     */
    public $masterIp;

    /**
     * @TarsProperty(order = 5, required = true, type = "string")
     *
     * @var string
     */
    public $slaveIp;

    /**
     * @TarsProperty(order = 6, required = true, type = "int")
     *
     * @var int
     */
    public $depth;

    /**
     * @TarsProperty(order = 7, required = true, type = "int")
     *
     * @var int
     */
    public $width;

    /**
     * @TarsProperty(order = 8, required = true, type = "int")
     *
     * @var int
     */
    public $parentWidth;
}
