<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use Monolog\Test\TestCase;
use wenbinye\tars\registry\EndpointF;
use wenbinye\tars\registry\QueryFClient;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\framework\PhpDiContainerFactory;

class RegistryTest extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
    }

    public function testQueryLogServant()
    {
        $container = (new PhpDiContainerFactory())->create();
        $queryFClient = $container->get(QueryFClient::class);
        $objectById = $queryFClient->findObjectById('tars.tarslog.LogObj');
        // var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }
}
