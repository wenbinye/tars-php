<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use kuiper\event\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\Console\Application;

class BootstrapEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    /**
     * @var Application
     */
    private $app;

    /**
     * Bootstrap constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return Application
     */
    public function getApp(): Application
    {
        return $this->app;
    }
}
