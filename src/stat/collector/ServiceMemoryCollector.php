<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class ServiceMemoryCollector extends AbstractAvgCollector
{
    public function getValues(): array
    {
        exec("ps -e -ww -o 'rsz,cmd' | grep {$this->getServerName()} | grep -v grep | awk '{count += $1}; END {print count}'",
            $serverMemInfo);
        if ($serverMemInfo[0]) {
            return [
                $this->getServerName().'.memoryUsage' => $serverMemInfo[0],
            ];
        }

        return [];
    }
}
