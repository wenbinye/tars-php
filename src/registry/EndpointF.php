<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\protocol\annotation\TarsStructProperty;

final class EndpointF
{
    /**
     * @TarsStructProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $host;

    /**
     * @TarsStructProperty(order = 1, required = true, type = "int")
     *
     * @var int
     */
    public $port;

    /**
     * @TarsStructProperty(order = 2, required = true, type = "int")
     *
     * @var int
     */
    public $timeout;

    /**
     * @TarsStructProperty(order = 3, required = true, type = "int")
     *
     * @var int
     */
    public $istcp;

    /**
     * @TarsStructProperty(order = 4, required = true, type = "int")
     *
     * @var int
     */
    public $grid;

    /**
     * @TarsStructProperty(order = 5, required = false, type = "int")
     *
     * @var int
     */
    public $groupworkid;

    /**
     * @TarsStructProperty(order = 6, required = false, type = "int")
     *
     * @var int
     */
    public $grouprealid;

    /**
     * @TarsStructProperty(order = 7, required = false, type = "string")
     *
     * @var string
     */
    public $setId;

    /**
     * @TarsStructProperty(order = 8, required = false, type = "int")
     *
     * @var int
     */
    public $qos;

    /**
     * @TarsStructProperty(order = 9, required = false, type = "int")
     *
     * @var int
     */
    public $bakFlag;

    /**
     * @TarsStructProperty(order = 11, required = false, type = "int")
     *
     * @var int
     */
    public $weight;

    /**
     * @TarsStructProperty(order = 12, required = false, type = "int")
     *
     * @var int
     */
    public $weightType;
}
