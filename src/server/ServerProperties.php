<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

class ServerProperties
{
    /**
     * @var string
     */
    private $app;

    /**
     * @var string
     */
    private $server;

    /**
     * @var bool
     */
    private $daemonize;
    /**
     * @var int
     */
    private $taskWorkerNum;
    /**
     * @var int
     */
    private $dispatchMode;

    /**
     * @var string
     */
    private $basePath;
    /**
     * @var string
     */
    private $dataPath;
    /**
     * @var string
     */
    private $logPath;
    /**
     * @var string
     */
    private $logLevel;
    /**
     * @var int
     */
    private $logSize;

    /**
     * @var TarsRoute
     */
    private $local;
    /**
     * @var string
     */
    private $logServantName;
    /**
     * @var string
     */
    private $configServantName;
    /**
     * @var string
     */
    private $notifyServantName;
}
