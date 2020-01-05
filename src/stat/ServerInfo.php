<?php

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class ServerInfo {
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     * @var string
     */
     public $application;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     * @var string
     */
     public $serverName;

    /**
     * @TarsProperty(order = 2, required = true, type = "int")
     * @var int
     */
     public $pid;

    /**
     * @TarsProperty(order = 3, required = false, type = "string")
     * @var string
     */
     public $adapter;

}