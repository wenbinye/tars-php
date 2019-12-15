<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use wenbinye\tars\server\task\TaskProcessorInterface;

class TaskEventListener implements EventListenerInterface
{
    /**
     * @var TaskProcessorInterface
     */
    private $taskProcessor;

    /**
     * @param TaskEvent $event
     */
    public function __invoke($event): void
    {
        $this->taskProcessor->process($event->getData());
    }
}
