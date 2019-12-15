<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

interface TaskProcessorInterface
{
    /**
     * @param object $task
     */
    public function process($task): void;
}
