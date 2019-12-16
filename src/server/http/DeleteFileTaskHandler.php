<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

use Swoole\Timer;
use wenbinye\tars\server\task\TaskHandlerInterface;

class DeleteFileTaskHandler implements TaskHandlerInterface
{
    /**
     * @param DeleteFileTask $task
     */
    public function handle($task)
    {
        if ($task->getDelay() > 0) {
            Timer::after($task->getDelay(), static function () use ($task) {
                @unlink($task->getFileName());
            });
        } else {
            @unlink($task->getFileName());
        }
    }
}
