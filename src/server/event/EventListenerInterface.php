<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

interface EventListenerInterface
{
    /**
     * @param SwooleServerEvent $event
     */
    public function onEvent($event): void;
}
