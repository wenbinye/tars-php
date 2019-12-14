<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class WorkerErrorEvent extends SwooleServerEvent
{
    /**
     * @var int
     */
    private $workerId;
    /**
     * @var int
     */
    private $workerPid;
    /**
     * @var int
     */
    private $exitCode;
    /**
     * @var int
     */
    private $signal;

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    public function getWorkerPid(): int
    {
        return $this->workerPid;
    }

    public function setWorkerPid(int $workerPid): void
    {
        $this->workerPid = $workerPid;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function getSignal(): int
    {
        return $this->signal;
    }

    public function setSignal(int $signal): void
    {
        $this->signal = $signal;
    }
}
