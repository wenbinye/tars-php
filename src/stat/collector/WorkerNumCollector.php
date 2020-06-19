<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class WorkerNumCollector extends AbstractAvgCollector
{
    public function getValues(): array
    {
        exec("ps wwaux | grep {$this->getServerName()} | grep 'worker process' | grep -v grep | wc -l",
            $swooleWorkerNum);
        if (isset($swooleWorkerNum[0])) {
            return [
                $this->getServerName().'.workerNum' => $swooleWorkerNum[0],
            ];
        }

        return [];
    }
}
