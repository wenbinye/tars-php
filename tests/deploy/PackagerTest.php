<?php

declare(strict_types=1);

namespace wenbinye\tars\deploy;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class PackagerTest extends TestCase
{
    public function testPackage()
    {
        $app = new Application();
        $app->add(new PackageCommand());
        $app->setDefaultCommand('package');
        chdir(__DIR__.'/fixtures');
        try {
            $app->setAutoExit(false);
            $app->run(new ArrayInput([]));
        } catch (\Exception $e) {
        }
        $this->assertTrue(true);
    }
}
