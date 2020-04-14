<?php

declare(strict_types=1);

namespace wenbinye\tars\log;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use wenbinye\tars\server\ServerProperties;

/**
 * MonoLog Handler with TARS log servant backend.
 *
 * Class TarsLogHandler
 */
class TarsLogHandler extends AbstractProcessingHandler
{
    /**
     * @var ServerProperties
     */
    private $serverProperties;
    /**
     * @var LogServant
     */
    private $logClient;
    /**
     * @var string
     */
    private $dateFormat;

    public function __construct(ServerProperties $serverProperties, LogServant $logClient, string $dateFormat = '%Y%m%d', bool $bubble = true)
    {
        $logLevel = constant(Logger::class.'::'.strtoupper($serverProperties->getLogLevel()));
        parent::__construct($logLevel, $bubble);

        $this->serverProperties = $serverProperties;
        $this->logClient = $logClient;
        $this->dateFormat = $dateFormat;
    }

    /**
     * Writes the record down to the log of the implementing handler.
     *
     * @throws \Exception
     */
    protected function write(array $record): void
    {
        $this->logClient->logger($this->serverProperties->getApp(), $this->serverProperties->getServer(),
            $record['channel'], $this->dateFormat, [$record['formatted']]);
    }
}
