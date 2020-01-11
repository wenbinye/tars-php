<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\swoole\event\WorkerStartEvent;
use kuiper\swoole\listener\EventListenerInterface;
use kuiper\swoole\task\QueueInterface;
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
