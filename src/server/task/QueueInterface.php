<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

interface QueueInterface
{
    /**
     * 提交任务到队列.
     *
     * @param object $task
     */
    public function put($task, int $workerId = -1, callable $onFinish = null): int;
}
