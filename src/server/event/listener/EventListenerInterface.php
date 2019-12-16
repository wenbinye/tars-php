<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\SwooleServerEvent;

interface EventListenerInterface
{
    /**
     * @param SwooleServerEvent $event
     */
    public function __invoke($event): void;
}
