<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use DI\Annotation\Inject;
use function DI\autowire;
use DI\Definition\FactoryDefinition;
use DI\Definition\ObjectDefinition;
use function DI\get;
use function DI\value;
use kuiper\annotations\AnnotationReader;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use kuiper\di\AwareInjection;
use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerBuilderAwareTrait;
use kuiper\di\DefinitionConfiguration;
use kuiper\di\PropertiesDefinitionSource;
use kuiper\event\EventDispatcherAwareInterface;
use kuiper\helper\PropertyResolverInterface;
use kuiper\logger\LoggerFactory;
use kuiper\logger\LoggerFactoryInterface;
use kuiper\swoole\annotation\PooledAnnotationReader;
use kuiper\swoole\pool\PoolFactory;
use kuiper\swoole\pool\PoolFactoryInterface;
use kuiper\swoole\task\DispatcherInterface;
use kuiper\swoole\task\Queue;
use kuiper\swoole\task\QueueInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactoryInterface;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\listener\BootstrapEventListener;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\ServerProperties;

/**
 * @Configuration()
 */
class FoundationConfiguration implements DefinitionConfiguration
{
    use ContainerBuilderAwareTrait;

    public function getDefinitions(): array
    {
        $this->containerBuilder->addAwareInjection(new AwareInjection(
            LoggerAwareInterface::class,
            'setLogger',
            static function (ObjectDefinition $definition): array {
                $name = $definition->getName().'.logger';
                $class = $definition->getClassName();
                $loggerDefinition = new FactoryDefinition(
                    $name, static function (LoggerFactoryInterface $loggerFactory) use ($class): LoggerInterface {
                        return $loggerFactory->create($class);
                    });

                return [$loggerDefinition];
            }));
        $this->containerBuilder->addAwareInjection(AwareInjection::create(ContainerAwareInterface::class));
        $this->containerBuilder->addAwareInjection(AwareInjection::create(EventDispatcherAwareInterface::class));
        $this->containerBuilder->addDefinitions(new PropertiesDefinitionSource(Config::getInstance()));

        return [
            Config::class => value(Config::getInstance()),
            PropertyResolverInterface::class => get(Config::class),
            QueueInterface::class => autowire(Queue::class),
            DispatcherInterface::class => get(QueueInterface::class),
            MethodMetadataFactoryInterface::class => autowire(MethodMetadataFactory::class),
            PsrEventDispatcher::class => get(EventDispatcherInterface::class),
        ];
    }

    /**
     * @Bean
     *
     * @return AnnotationReaderInterface
     */
    public function annotationReader(PoolFactoryInterface $poolFactory): AnnotationReaderInterface
    {
        return new PooledAnnotationReader($poolFactory);
    }

    /**
     * @Bean("coroutineEnabled")
     *
     * @return bool
     */
    public function coroutineEnabled(): bool
    {
        return !Config::getInstance()->getBool('application.server.enable-php-server', false);
    }

    /**
     * @Bean()
     */
    public function validator(AnnotationReaderInterface $annotationReader): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping($annotationReader)
            ->getValidator();
    }

    /**
     * @Bean()
     */
    public function eventDispatcher(BootstrapEventListener $bootstrapEventListener): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $bootstrapEventListener->setEventDispatcher($eventDispatcher);
        $eventDispatcher->addListener($bootstrapEventListener->getSubscribedEvent(), $bootstrapEventListener);

        return $eventDispatcher;
    }

    /**
     * @Bean()
     */
    public function serverProperties(PropertyLoader $propertyLoader): ServerProperties
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $propertyLoader->loadServerProperties(Config::getInstance());
    }

    /**
     * @Bean()
     */
    public function clientProperties(PropertyLoader $propertyLoader): ClientProperties
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $propertyLoader->loadClientProperties(Config::getInstance());
    }

    /**
     * @Bean()
     */
    public function logger(LoggerFactoryInterface $loggerFactory): LoggerInterface
    {
        return $loggerFactory->create();
    }

    /**
     * @Bean()
     * @Inject({"loggingConfig" = "application.logging"})
     */
    public function loggerFactory(ContainerInterface $container, ?array $loggingConfig): LoggerFactoryInterface
    {
        if (!isset($loggingConfig['loggers']['root'])) {
            $loggingConfig['loggers']['root'] = ['console' => true];
        }

        return new LoggerFactory($container, $loggingConfig);
    }

    /**
     * @Bean()
     */
    public function packer(AnnotationReaderInterface $annotationReader): PackerInterface
    {
        return new Packer($annotationReader);
    }

    /**
     * @Bean()
     * @Inject({"poolConfig" = "application.pool"})
     */
    public function poolFactory(?array $poolConfig, LoggerFactoryInterface $loggerFactory, EventDispatcherInterface $eventDispatcher): PoolFactoryInterface
    {
        return new PoolFactory($this->coroutineEnabled(),
            $poolConfig ?? [], $loggerFactory->create(PoolFactory::class), $eventDispatcher);
    }
}
