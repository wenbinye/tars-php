<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\rpc\fixtures\FooServant;

class ServantProxyGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $servantProxyGenerator = new ServantProxyGenerator(AnnotationReader::getInstance());
        $class = $servantProxyGenerator->generate(FooServant::class);
        $this->assertNotNull($class);
    }
}
