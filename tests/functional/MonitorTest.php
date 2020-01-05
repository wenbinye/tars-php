<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use wenbinye\tars\stat\MonitorInterface;

class MonitorTest extends FunctionalTestCase
{
    public function testMonitor()
    {
        $monitor = $this->getContainer()->get(MonitorInterface::class);
        $monitor->monitor();
    }
}
