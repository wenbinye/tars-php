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
        $objectById = $queryFClient->findObjectById('tars.tarslog.LogObj');
        // var_export($objectById);
        $this->assertIsArray($objectById);
        $this->assertInstanceOf(EndpointF::class, $objectById[0]);
    }
}
