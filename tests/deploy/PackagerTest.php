<?php

declare(strict_types=1);

namespace wenbinye\tars\deploy;

use PHPUnit\Framework\TestCase;

class PackagerTest extends TestCase
{
    public function testPackage()
    {
        $packager = new Packager(__DIR__.'/fixtures');
        $packageFile = $packager->execute();
        $this->assertFileExists($packageFile);
        @unlink($packageFile);
    }
}
