<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class EndpointF
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $host;

    /**
     * @TarsProperty(order = 1, required = true, type = "int")
     *
     * @var int
     */
    public $port;

    /**
     * @TarsProperty(order = 2, required = true, type = "int")
     *
     * @var int
     */
    public $timeout;

    /**
     * @TarsProperty(order = 3, required = true, type = "int")
     *
     * @var int
     */
    public $istcp;

    /**
     * @TarsProperty(order = 4, required = true, type = "int")
     *
     * @var int
     */
    public $grid;

    /**
     * @TarsProperty(order = 5, required = false, type = "int")
     *
     * @var int
     */
    public $groupworkid;

    /**
     * @TarsProperty(order = 6, required = false, type = "int")
     *
     * @var int
     */
    public $grouprealid;

    /**
     * @TarsProperty(order = 7, required = false, type = "string")
     *
     * @var string
     */
    public $setId;

    /**
     * @TarsProperty(order = 8, required = false, type = "int")
     *
     * @var int
     */
    public $qos;

    /**
     * @TarsProperty(order = 9, required = false, type = "int")
     *
     * @var int
     */
    public $bakFlag;

    /**
     * @TarsProperty(order = 11, required = false, type = "int")
     *
     * @var int
     */
    public $weight;

    /**
     * @TarsProperty(order = 12, required = false, type = "int")
     *
     * @var int
     */
    public $weightType;
}
