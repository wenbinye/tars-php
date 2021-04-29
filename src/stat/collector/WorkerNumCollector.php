<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class WorkerNumCollector extends AbstractCollector
{
    protected $policy = 'Min';

    public function getValues(): array
    {
        exec("ps wwaux | grep {$this->getServerName()} | grep -v grep | wc -l",
            $swooleWorkerNum);
        if (isset($swooleWorkerNum[0])) {
            return [
                'workerNum' => $swooleWorkerNum[0],
            ];
        }

        return [];
    }
}
