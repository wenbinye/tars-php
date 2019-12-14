<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use PHPUnit\Framework\TestCase;

class SwooleServerTypeTest extends TestCase
{
    public function testAllServerTypeHasSettings()
    {
        foreach (SwooleServerType::instances() as $serverType) {
            $this->assertIsArray($serverType->settings);
        }
    }

    public function testAllServerTypeHasEvents()
    {
        foreach (SwooleServerType::instances() as $serverType) {
            $this->assertIsArray($serverType->events);
        }
    }
}
