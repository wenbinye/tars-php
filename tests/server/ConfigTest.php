<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testParse()
    {
        $result = Config::parse('<app>
<server>
foo=1
</server>
<client>
bar=2
</client>
</app>');
        $this->assertEquals(['app' => [
            'server' => ['foo' => '1'],
            'client' => ['bar' => '2'],
        ]], $result->toArray());
    }

    public function testParseFile()
    {
        $config = Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $clientProperties = ClientProperties::fromConfig($config, $annotationReader);
        $serverProperties = ServerProperties::fromConfig($config, $annotationReader);
        var_export([$clientProperties, $serverProperties]);
        // var_export($result->toArray());
    }
}
