<?php

declare(strict_types=1);

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0
 */

namespace wenbinye\tars\client;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class ServerInfo
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string|null
     */
    public $application;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     *
     * @var string|null
     */
    public $serverName;

    /**
     * @TarsProperty(order = 2, required = true, type = "int")
     *
     * @var int|null
     */
    public $pid;

    /**
     * @TarsProperty(order = 3, required = false, type = "string")
     *
     * @var string|null
     */
    public $adapter;
}
