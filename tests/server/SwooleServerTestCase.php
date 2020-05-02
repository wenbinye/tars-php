<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use function DI\factory;
use kuiper\annotations\AnnotationReader;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\di\ContainerBuilder;
use kuiper\swoole\event\ManagerStartEvent;
use kuiper\swoole\event\StartEvent;
use kuiper\swoole\listener\ManagerStartEventListener;
use kuiper\swoole\listener\StartEventListener;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class SwooleServerTestCase extends TestCase
{
    public function createContainer(): ContainerInterface
    {
        $config = Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
        $config->tars->application->client->merge([
            'keep-alive-interval' => '10000',
        ]);
        $config->tars->application->server->merge([
            'daemonize' => 'false',
            'worker_num' => '1',
            'PHPTest.PHPHttpServer.objAdapter' => [
                'protocol' => 'http',
            ],
        ]);
        $containerBuilder = new ContainerBuilder();
        $containerBuilder
            ->addDefinitions([
                Config::class => $config,
                AnnotationReaderInterface::class => factory([AnnotationReader::class, 'getInstance']),
                ValidatorInterface::class => function (AnnotationReaderInterface $annotationReader) {
                    return Validation::createValidatorBuilder()
                        ->enableAnnotationMapping($annotationReader)
                        ->getValidator();
                },
                ServerProperties::class => factory([PropertyLoader::class, 'loadServerProperties']),
                ClientProperties::class => factory([PropertyLoader::class, 'loadClientProperties']),
                LoggerInterface::class => function () {
                    return new Logger('test', [new ErrorLogHandler()]);
                },
                EventDispatcherInterface::class => function (ContainerInterface $container) {
                    $dispatcher = new EventDispatcher();
                    $dispatcher->addListener(StartEvent::class, $container->get(StartEventListener::class));
                    $dispatcher->addListener(ManagerStartEvent::class, $container->get(ManagerStartEventListener::class));

                    return $dispatcher;
                },
            ]);

        return $containerBuilder->build();
    }
}
