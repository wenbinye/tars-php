<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

class ClientProperties
{
    /**
     * @var int
     */
    private $asyncThread;
    /**
     * @var TarsRoute
     */
    private $locator;
    /**
     * @var int
     */
    private $syncInvokeTimeout;
    /**
     * @var int
     */
    private $asyncInvokeTimeout;
    /**
     * @var int
     */
    private $refresEndpointInterval;
    /**
     * @var int
     */
    private $reportInterval;
    /**
     * @var string
     */
    private $statServantName;
    /**
     * @var string
     */
    private $propertyServantName;
    /**
     * @var string
     */
    private $moduleName;
    /**
     * @var int
     */
    private $sampleRate;
    /**
     * @var int
     */
    private $maxSampleCount;
}
