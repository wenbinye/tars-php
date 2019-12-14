<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class WorkerStartEvent extends SwooleServerEvent
{
    /**
     * @var int
     */
    private $workerId;

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }
}
