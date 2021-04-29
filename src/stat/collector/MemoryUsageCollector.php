<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class MemoryUsageCollector extends AbstractCollector
{
    public function getValues(): array
    {
        return [
            $this->getServerName().'.memoryUsage' => memory_get_usage(true),
            $this->getServerName().'.peakMemoryUsage' => memory_get_peak_usage(true),
        ];
    }
}
