<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Test\TestCase;
use wenbinye\tars\log\LogServant;

class TarsClientGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $generator = new TarsClientGenerator($annotationReader);
        $clientClass = $generator->generate(LogServant::class);
        // $this->assertTrue(class_exists($clientClass));

        /** @var LogServant $client */
        $tarsClient = \Mockery::mock(TarsClient::class);
        $tarsClient->shouldReceive('send')
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
        $client = new $clientClass($tarsClient);

        $this->assertInstanceOf(LogServant::class, $client);
        $client->logger('app', 'server', 'file', 'format', ['buffer']);
    }
}
