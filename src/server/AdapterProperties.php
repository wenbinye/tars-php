<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

class AdapterProperties
{
    /**
     * @var TarsRoute
     */
    private $endpoint;
    /**
     * @var int
     */
    private $maxConnections;
    /**
     * @var string
     */
    private $protocol;
    /**
     * @var int
     */
    private $queueCapacity;
    /**
     * @var int
     */
    private $queueTimeout;
    /**
     * @var string
     */
    private $servantName;
    /**
     * @var int
     */
    private $threads;
}
