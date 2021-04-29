<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class SystemMemoryCollector extends AbstractCollector
{
    public function getValues(): array
    {
        exec('free -m | grep Mem', $sysMemInfo);
        preg_match_all("/\d+/s", $sysMemInfo[0], $matches);
        if (isset($matches[0][0])) {
            return ['system.memoryUsage' => $matches[0][0]];
        }

        return [];
    }
}
