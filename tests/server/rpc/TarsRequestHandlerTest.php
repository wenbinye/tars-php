<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Test\TestCase;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\server\Config;

class TarsRequestHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function testName()
    {
        $annotationReader = new AnnotationReader();
        $fooServantImpl = new FooServantImpl();
        $packer = new Packer(new TarsTypeFactory($annotationReader));
        $tarsRequestHandler = new TarsRequestHandler([$fooServantImpl], $annotationReader, $packer);
        $servants = $this->readAttribute($tarsRequestHandler, 'servants');
        var_export($servants);
    }
}

/**
 * @TarsServant("foo")
 * Interface FooServant
 */
interface FooServant
{
}

/**
 * Class FooServant.
 */
class FooServantImpl implements FooServant
{
}
