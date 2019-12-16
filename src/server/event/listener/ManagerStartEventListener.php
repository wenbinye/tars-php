<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\ManagerStartEvent;

class ManagerStartEventListener implements EventListenerInterface
{
    /**
     * @param ManagerStartEvent $event
     */
    public function __invoke($event): void
    {
        @cli_set_process_title($event->getServer()->getServerProperties()->getServerName().': manager process');
    }
}
