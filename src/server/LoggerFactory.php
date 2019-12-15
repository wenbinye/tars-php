<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    /**
     * @throws \Exception
     */
    public function create(): LoggerInterface
    {
        $logger = new Logger($this->serverProperties->getServerName());
        $loggerLevelName = strtoupper($this->serverProperties->getLogLevel());

        $loggerLevel = constant(Logger::class.'::'.$loggerLevelName);
        if (!isset($loggerLevel)) {
            throw new \InvalidArgumentException("Unknown logger level '{$loggerLevelName}'");
        }
        $logPath = sprintf('%s/%s/%s/', rtrim($this->serverProperties->getLogPath(), '/'),
            $this->serverProperties->getApp(), $this->serverProperties->getServer());
        $logger->pushHandler(new StreamHandler($logPath.$this->serverProperties->getServerName().'.log', $loggerLevel));
        $logger->pushHandler(new StreamHandler($logPath.'log_'.strtolower($loggerLevelName).'.log', $loggerLevel));

        return $logger;
    }
}
