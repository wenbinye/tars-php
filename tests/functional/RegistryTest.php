<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use wenbinye\tars\client\EndpointF;
use wenbinye\tars\client\QueryFServant;

class RegistryTest extends FunctionalTestCase
{
    public function testQueryLogServant()
    {
        $container = $this->getContainer();
        $queryFClient = $container->get(QueryFServant::class);
        $id = 'tars.tarslog.LogObj';
        // $id = 'tars.tarsnode.ServerObj';
        $objectById = $queryFClient->findObjectById($id);
        var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }

    public function testQueryStatServant()
    {
        $container = $this->getContainer();
        $queryFClient = $container->get(QueryFServant::class);
        $id = 'tars.tarsstat.StatObj';
        // $id = 'tars.tarsnode.ServerObj';
        $objectById = $queryFClient->findObjectById($id);
        var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }

    public function testQueryConfigServant()
    {
        $container = $this->getContainer();
        $queryFClient = $container->get(QueryFServant::class);
        $id = 'tars.tarsconfig.ConfigObj';
        // $id = 'tars.tarsnode.ServerObj';
        $objectById = $queryFClient->findObjectById($id);
        var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }
}
