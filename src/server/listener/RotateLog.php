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
use wenbinye\tars\server\task\LogRotate;

class RotateLog implements EventListenerInterface, LoggerAwareInterface
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
     * @var LogRotate
     */
    private $logRotate;

    /**
     * WorkerStartEventListener constructor.
     */
    public function __construct(QueueInterface $taskQueue, LoggerFactoryInterface $loggerFactory, LogRotate $logRotate)
    {
        $this->taskQueue = $taskQueue;
        $this->logRotate = $logRotate;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param WorkerStartEvent $event
     */
    public function __invoke($event): void
    {
        if (0 === $event->getWorkerId()) {
            $this->logger->debug(static::TAG.'add rotate log task');
            $this->taskQueue->put($this->logRotate);
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
