<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class ManagerStartEventListener implements EventListenerInterface
{
    /**
     * @param ManagerStartEvent $event
     */
    public function onEvent($event): void
    {
        @cli_set_process_title($event->getServer()->getServerProperties()->getServerName().': manager process');
    }
}
