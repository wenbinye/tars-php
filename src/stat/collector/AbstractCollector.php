<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

use wenbinye\tars\server\ServerProperties;

abstract class AbstractCollector implements CollectorInterface
{
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * @var string
     */
    protected $policy = 'Max';

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
        return $this->policy;
    }
}
