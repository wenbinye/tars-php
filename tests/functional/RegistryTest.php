<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use wenbinye\tars\registry\EndpointF;
use wenbinye\tars\registry\QueryFServant;

class RegistryTest extends FunctionalTestCase
{
    public function testQueryLogServant()
    {
        $queryFClient = $this->getContainer()->get(QueryFServant::class);
        $id = 'tars.tarslog.LogObj';
        // $id = 'tars.tarsnode.ServerObj';
        $objectById = $queryFClient->findObjectById($id);
        // var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }
}
