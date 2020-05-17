<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use function DI\autowire;
use function DI\factory;
use function DI\get;
use function DI\value;
use kuiper\annotations\AnnotationReader;
use kuiper\annotations\AnnotationReaderInterface;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use kuiper\di\AwareInjection;
use kuiper\di\ContainerBuilderAwareTrait;
use kuiper\di\DefinitionConfiguration;
use kuiper\di\PropertiesDefinitionSource;
use kuiper\helper\PropertyResolverInterface;
use kuiper\swoole\task\DispatcherInterface;
use kuiper\swoole\task\Queue;
use kuiper\swoole\task\QueueInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
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
use wenbinye\tars\server\Protocol;
use wenbinye\tars\server\ServerProperties;

/**
 * @Configuration()
 */
class FoundationConfiguration implements DefinitionConfiguration
{
    use ContainerBuilderAwareTrait;

    public function getDefinitions(): array
    {
        $this->containerBuilder->addAwareInjection(AwareInjection::create(LoggerAwareInterface::class));
        $this->containerBuilder->addDefinitions(new PropertiesDefinitionSource(Config::getInstance()));

        return [
            Config::class => value(Config::getInstance()),
            PropertyResolverInterface::class => get(Config::class),
            AnnotationReaderInterface::class => factory([AnnotationReader::class, 'getInstance']),
            QueueInterface::class => autowire(Queue::class),
            DispatcherInterface::class => get(QueueInterface::class),
            MethodMetadataFactoryInterface::class => autowire(MethodMetadataFactory::class),
            PsrEventDispatcher::class => get(EventDispatcherInterface::class),
        ];
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
        $config = Config::getInstance();
        /** @noinspection PhpUnhandledExceptionInspection */
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $protocol = $serverProperties->getPrimaryAdapter()->getProtocol();
        $config->merge([
            'application' => [
                'protocol' => $protocol,
                'http_protocol' => Protocol::fromValue($protocol)->isHttpProtocol() ? $protocol : null,
            ],
        ]);

        return $serverProperties;
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
    public function logger(ServerProperties $serverProperties): LoggerInterface
    {
        $logger = new Logger($serverProperties->getServerName());
        $loggerLevelName = strtoupper($serverProperties->getLogLevel());

        $loggerLevel = constant(Logger::class.'::'.$loggerLevelName);
        if (!isset($loggerLevel)) {
            throw new \InvalidArgumentException("Unknown logger level '{$loggerLevelName}'");
        }

        $logPath = $serverProperties->getAppLogPath().'/';
        $logger->pushHandler(new StreamHandler($logPath.$serverProperties->getServerName().'.log', $loggerLevel));
        $handler = new StreamHandler($logPath.'log_'.strtolower($loggerLevelName).'.log', $loggerLevel);
        $lineFormatter = new LineFormatter();
        $lineFormatter->allowInlineLineBreaks();
        $handler->setFormatter($lineFormatter);
        $logger->pushHandler($handler);
        $logger->pushProcessor(new ProcessIdProcessor());

        return $logger;
    }

    /**
     * @Bean()
     */
    public function packer(AnnotationReaderInterface $annotationReader): PackerInterface
    {
        return new Packer($annotationReader);
    }
}
