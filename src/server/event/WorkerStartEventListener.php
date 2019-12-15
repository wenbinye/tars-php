<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use wenbinye\tars\server\task\QueueInterface;
use wenbinye\tars\server\task\ReportTask;

class WorkerStartEventListener implements EventListenerInterface
{
    /**
     * @var QueueInterface
     */
    private $taskQueue;

    /**
     * @param WorkerStartEvent $event
     */
    public function __invoke($event): void
    {
        $this->setProcessTitle($event);
        if (0 === $event->getWorkerId()) {
            $this->startKeepAliveTask();
        }
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

    private function startKeepAliveTask(): void
    {
        $this->taskQueue->put(new ReportTask());
    }
}
