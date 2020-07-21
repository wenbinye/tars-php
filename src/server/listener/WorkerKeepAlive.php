<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\swoole\event\WorkerStartEvent;
use kuiper\swoole\task\QueueInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\server\task\ReportTask;

class WorkerKeepAlive implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var QueueInterface
     */
    private $taskQueue;

    /**
     * WorkerStartEventListener constructor.
     */
    public function __construct(QueueInterface $taskQueue)
    {
        $this->taskQueue = $taskQueue;
    }

    /**
     * @param WorkerStartEvent $event
     */
    public function __invoke($event): void
    {
        if (0 === $event->getWorkerId()) {
            $this->taskQueue->put(new ReportTask());
        }
    }

    public function getSubscribedEvent(): string
    {
        return WorkerStartEvent::class;
    }
}
