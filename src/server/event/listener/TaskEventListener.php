<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\TaskEvent;
use wenbinye\tars\server\task\TaskProcessorInterface;

class TaskEventListener implements EventListenerInterface
{
    /**
     * @var TaskProcessorInterface
     */
    private $taskProcessor;

    /**
     * TaskEventListener constructor.
     */
    public function __construct(TaskProcessorInterface $taskProcessor)
    {
        $this->taskProcessor = $taskProcessor;
    }

    /**
     * @param TaskEvent $event
     */
    public function __invoke($event): void
    {
        $this->taskProcessor->process($event->getData());
    }

    public function getSubscribedEvent(): string
    {
        return TaskEvent::class;
    }
}
