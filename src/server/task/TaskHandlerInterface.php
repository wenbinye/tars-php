<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

interface TaskHandlerInterface
{
    /**
     * @param object $task
     *
     * @return mixed
     */
    public function handle($task);
}
