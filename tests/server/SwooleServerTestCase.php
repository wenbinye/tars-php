<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use function DI\autowire;
use DI\ContainerBuilder;
use function DI\factory;
use function DI\get;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\server\event\ManagerStartEvent;
use wenbinye\tars\server\event\ManagerStartEventListener;
use wenbinye\tars\server\event\StartEvent;
use wenbinye\tars\server\event\StartEventListener;
use wenbinye\tars\server\task\Queue;
use wenbinye\tars\server\task\QueueInterface;

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
            'PHPTest.PHPHttpServer.objAdapter' => [
                'protocol' => 'http',
            ],
        ]);
        $containerBuilder = new ContainerBuilder(AutoAwareContainer::class);
        $containerBuilder->useAutowiring(true)
            ->addDefinitions([
            Config::class => $config,
            Reader::class => function () {
                AnnotationRegistry::registerLoader('class_exists');

                return new AnnotationReader();
            },
            ValidatorInterface::class => function (Reader $annotationReader) {
                return Validation::createValidatorBuilder()
                    ->enableAnnotationMapping($annotationReader)
                    ->getValidator();
            },
            ServerProperties::class => factory([PropertyLoader::class, 'loadServerProperties']),
            ClientProperties::class => factory([PropertyLoader::class, 'loadClientProperties']),
            LoggerInterface::class => function () {
                return new Logger('test', [new ErrorLogHandler()]);
            },
            ServerInterface::class => autowire(SwooleServer::class),
            SwooleServer::class => get(ServerInterface::class),
            QueueInterface::class => autowire(Queue::class),
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
