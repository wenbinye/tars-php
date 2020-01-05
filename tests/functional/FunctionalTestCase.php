<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\framework\PhpDiContainerFactory;
use wenbinye\tars\server\framework\ServerConfiguration;

abstract class FunctionalTestCase extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
    }

    public function getContainer(): ContainerInterface
    {
        $containerFactory = new PhpDiContainerFactory();
        $containerFactory->getBeanConfigurationSource()
            ->addConfiguration(new ServerConfiguration());

        return $containerFactory->create();
    }
}
