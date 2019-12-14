<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use PHPUnit\Framework\TestCase;

class SwooleServerSettingTest extends TestCase
{
    public function testEverySettingHasType()
    {
        foreach (SwooleServerSetting::instances() as $setting) {
            $this->assertNotNull($setting->type);
        }
    }
}
