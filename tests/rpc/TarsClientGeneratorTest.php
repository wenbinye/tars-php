<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use Monolog\Test\TestCase;
use wenbinye\tars\client\LogServant;
use wenbinye\tars\rpc\message\MethodMetadataFactory;

class TarsClientGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $annotationReader = AnnotationReader::getInstance();
        $generator = new ServantProxyGenerator($annotationReader);
        $clientClass = $generator->generate(LogServant::class);

        // $this->assertTrue(class_exists($clientClass));

        /** @var LogServant $client */
        $tarsClient = \Mockery::mock(TarsClient::class);
        $client = new $clientClass($tarsClient);

        $tarsClient->shouldReceive('call')
            ->withArgs(function (...$args) use ($clientClass) {
                $this->assertInstanceOf($clientClass, $args[0]);
                $this->assertEquals(array_slice($args, 1), [
                    0 => 'logger',
                    1 => 'app',
                    2 => 'server',
                    3 => 'file',
                    4 => 'format',
                    5 => [
                            0 => 'buffer',
                        ],
                ]);

                return true;
            });

        $this->assertInstanceOf(LogServant::class, $client);
        $client->logger('app', 'server', 'file', 'format', ['buffer']);
    }

    public function testGenerateWithServant()
    {
        $annotationReader = AnnotationReader::getInstance();
        $generator = new ServantProxyGenerator($annotationReader);
        // echo $generator->createClassGenerator(LogServant::class, "foo.ser")->generate();
        $clientClass = $generator->generate(LogServant::class, 'foo.fooServer.LogObj');
        $tarsClient = \Mockery::mock(TarsClient::class);
        $client = new $clientClass($tarsClient);

        $factory = new MethodMetadataFactory($annotationReader);
        $methodMetadata = $factory->create($client, 'logger');
        // var_export($methodMetadata);
        $this->assertEquals($methodMetadata->getServantName(), 'foo.fooServer.LogObj');
    }
}
