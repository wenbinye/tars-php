<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\WorkerStartEvent;
use wenbinye\tars\server\task\QueueInterface;
use wenbinye\tars\server\task\ReportTask;

class WorkerKeepAlive implements EventListenerInterface
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
        if (0 === $event->getWorkerId()) {
            $this->taskQueue->put(new ReportTask());
        }
    }

    /**
     * WorkerStartEventListener constructor.
     */
    public function __construct(QueueInterface $taskQueue)
    {
        $this->taskQueue = $taskQueue;
    }

    public function getSubscribedEvent(): string
    {
        return WorkerStartEvent::class;
    }
}
