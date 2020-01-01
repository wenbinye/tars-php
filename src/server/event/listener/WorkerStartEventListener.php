<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\WorkerStartEvent;

class WorkerStartEventListener implements EventListenerInterface
{
    /**
     * @param WorkerStartEvent $event
     */
    public function __invoke($event): void
    {
        $this->setProcessTitle($event);
    }

    public function getSubscribedEvent(): string
    {
        return WorkerStartEvent::class;
    }

    private function setProcessTitle(WorkerStartEvent $event): void
    {
        $serverName = $event->getServer()->getServerProperties()->getServerName();
        if ($event->getSwooleServer()->taskworker) {
            @cli_set_process_title($serverName.": task worker {$event->getWorkerId()} process");
        } else {
            @cli_set_process_title($serverName.": worker {$event->getWorkerId()} process");
        }
    }
}
