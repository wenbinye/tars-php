<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\swoole\event\WorkerStartEvent;
use kuiper\swoole\listener\EventListenerInterface;
use kuiper\swoole\task\QueueInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
    public function __construct(QueueInterface $taskQueue, ?LoggerInterface $logger)
    {
        $this->taskQueue = $taskQueue;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @param WorkerStartEvent $event
     */
    public function __invoke($event): void
    {
        if (0 === $event->getWorkerId()) {
            $this->logger->debug(static::TAG.'send report task');
            $this->taskQueue->put(new ReportTask());
        }
    }

    public function getSubscribedEvent(): string
    {
        return WorkerStartEvent::class;
    }
}
