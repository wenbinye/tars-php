<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

use wenbinye\tars\server\ServerProperties;

abstract class AbstractAvgCollector implements CollectorInterface
{
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    public function getServerProperties(): ServerProperties
    {
        return $this->serverProperties;
    }

    public function getServerName(): string
    {
        return $this->serverProperties->getServerName();
    }

    public function getPolicy(): string
    {
        return 'Avg';
    }
}
