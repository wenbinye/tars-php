<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class SwooleServer implements ServerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * SwooleServer constructor.
     */
    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    public function start(): void
    {
    }

    public function stop(): void
    {
    }
}
