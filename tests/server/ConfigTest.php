<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ConfigTest extends TestCase
{
    public function testParse()
    {
        Config::parse('<app>
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
        ]], Config::getInstance()->toArray());
    }

    public function testParseFile()
    {
        Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
        $config = Config::getInstance();

        $config->tars->application->server->merge([
            'PHPTest.PHPHttpServer.objAdapter' => [
                'protocol' => 'http',
            ],
        ]);

        $annotationReader = AnnotationReader::getInstance();
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping($annotationReader)
            ->getValidator();
        $propertyLoader = new PropertyLoader($annotationReader, $validator);
        $clientProperties = $propertyLoader->loadClientProperties($config);
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $this->assertNotNull($clientProperties);
        $this->assertNotNull($clientProperties->getLocator());
        // var_export([$clientProperties, $serverProperties]);
        // var_export($result->toArray());
    }
}
