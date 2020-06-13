<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use wenbinye\tars\rpc\middleware\RequestLog;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\stat\MonitorInterface;

class MonitorTest extends FunctionalTestCase
{
    public function testMonitor()
    {
        $container = $this->getContainer();
        $monitor = $container->get(MonitorInterface::class);
        $container->get(TarsClientInterface::class)
            ->addMiddleware($container->get(RequestLog::class));
        $monitor->monitor();
        $this->assertTrue(true);
    }
}
