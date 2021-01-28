<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\logger\LoggerFactoryInterface;
use kuiper\swoole\event\WorkerStartEvent;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;

class StartLogRotate implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var LoggerFactoryInterface
     */
    private $loggerFactory;

    /**
     * @var int[]
     */
    private $fileInodes;

    /**
     * WorkerStartEventListener constructor.
     *
     * @param LoggerFactoryInterface $loggerFactory
     */
    public function __construct(LoggerFactoryInterface $loggerFactory)
    {
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        Assert::isInstanceOf($event, WorkerStartEvent::class);
        $this->checkLogFile();
        /* @var WorkerStartEvent $event */
        $event->getServer()->tick(10000, function (): void {
            $this->checkLogFile();
        });
    }

    private function checkLogFile(): void
    {
        clearstatcache();
        foreach ($this->loggerFactory->getLoggers() as $logger) {
            if (!($logger instanceof Logger)) {
                continue;
            }
            foreach ($logger->getHandlers() as $handler) {
                if (!($handler instanceof StreamHandler)) {
                    continue;
                }
                $fileExists = file_exists($handler->getUrl());
                if (!isset($this->fileInodes[$handler->getUrl()])) {
                    if (!$fileExists) {
                        continue;
                    }
                    $this->fileInodes[$handler->getUrl()] = fileinode($handler->getUrl());
                }
                if (!$fileExists || $this->fileInodes[$handler->getUrl()] !== fileinode($handler->getUrl())) {
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
