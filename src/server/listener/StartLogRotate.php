<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\logger\LoggerFactoryInterface;
use kuiper\swoole\event\WorkerStartEvent;
use kuiper\swoole\task\QueueInterface;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;
use wenbinye\tars\server\task\LogRotate;

class StartLogRotate implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var QueueInterface
     */
    private $taskQueue;

    /**
     * @var LoggerFactoryInterface
     */
    private $loggerFactory;

    /**
     * WorkerStartEventListener constructor.
     */
    public function __construct(QueueInterface $taskQueue, LoggerFactoryInterface $loggerFactory)
    {
        $this->taskQueue = $taskQueue;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        Assert::isInstanceOf($event, WorkerStartEvent::class);
        /** @var WorkerStartEvent $event */
        if (0 === $event->getWorkerId()) {
            $this->taskQueue->put(new LogRotate());
        }
        foreach ($this->loggerFactory->getLoggers() as $logger) {
            if ($logger instanceof Logger) {
                foreach ($logger->getHandlers() as $handler) {
                    $handler->close();
                }
            }
        }
    }

    public function getSubscribedEvent(): string
    {
        return WorkerStartEvent::class;
    }
}
