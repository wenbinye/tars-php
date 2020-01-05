<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use DI\Annotation\Inject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\di\annotation\Bean;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\registry\QueryFClient;
use wenbinye\tars\registry\RegistryConnectionFactory;
use wenbinye\tars\registry\SwooleTableRegistryCache;
use wenbinye\tars\rpc\ConnectionFactory;
use wenbinye\tars\rpc\ConnectionFactoryChain;
use wenbinye\tars\rpc\ConnectionFactoryInterface;
use wenbinye\tars\rpc\DefaultErrorHandler;
use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\RequestFactoryInterface;
use wenbinye\tars\rpc\RequestIdGenerator;
use wenbinye\tars\rpc\RequestIdGeneratorInterface;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\BeforeStartEvent;
use wenbinye\tars\server\event\listener\EventListenerInterface;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\TarsRequestHandler;
use wenbinye\tars\server\ServerProperties;

class ServerConfiguration
{
    /**
     * @Bean()
     */
    public function config(): Config
    {
        return Config::getInstance();
    }

    /**
     * @Bean
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function annotationReader(): Reader
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new AnnotationReader();
    }

    /**
     * @Bean()
     */
    public function validator(Reader $annotationReader): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping($annotationReader)
            ->getValidator();
    }

    /**
     * @Bean()
     */
    public function eventDispatcher(ContainerInterface $container, Config $config, LoggerInterface $logger): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(BeforeStartEvent::class, static function () use ($container, $dispatcher, $config, $logger) {
            foreach ($config->get('application.listeners', []) as $event => $listenerId) {
                $logger->debug("attach $listenerId");
                $listener = $container->get($listenerId);
                if ($listener instanceof EventListenerInterface) {
                    $dispatcher->addListener($listener->getSubscribedEvent(), $listener);
                } elseif (is_string($event)) {
                    $dispatcher->addListener($event, $listener);
                } else {
                    throw new \InvalidArgumentException("config application.listeners $listenerId does not bind to any event");
                }
            }
        });

        return $dispatcher;
    }

    /**
     * @Bean()
     */
    public function tarsRequestHandler(ContainerInterface $container, Config $config, Reader $reader, PackerInterface $packer): RequestHandlerInterface
    {
        $servants = [];
        $middlewares = [];
        foreach ($config->get('application.servants', []) as $servantId) {
            $servants[] = $container->get($servantId);
        }

        foreach ($config->get('application.servant_middlewares', []) as $middlewareId) {
            $middlewares[] = $container->get($middlewareId);
        }

        return new TarsRequestHandler($servants, $reader, $packer, $middlewares);
    }

    /**
     * @Bean()
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function serverProperties(PropertyLoader $propertyLoader, Config $config): ServerProperties
    {
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $configFile = $serverProperties->getBasePath().'/config.php';
        if (file_exists($configFile)) {
            $config->merge(require $configFile);
        }

        return $serverProperties;
    }

    /**
     * @Bean()
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function clientProperties(PropertyLoader $propertyLoader, Config $config): ClientProperties
    {
        return $propertyLoader->loadClientProperties($config);
    }

    /**
     * @Bean()
     *
     * @throws \Exception
     */
    public function logger(ServerProperties $serverProperties): LoggerInterface
    {
        $logger = new Logger($serverProperties->getServerName());
        $loggerLevelName = strtoupper($serverProperties->getLogLevel());

        $loggerLevel = constant(Logger::class.'::'.$loggerLevelName);
        if (!isset($loggerLevel)) {
            throw new \InvalidArgumentException("Unknown logger level '{$loggerLevelName}'");
        }
        $logPath = sprintf('%s/%s/%s/', rtrim($serverProperties->getLogPath(), '/'),
            $serverProperties->getApp(), $serverProperties->getServer());
        $logger->pushHandler(new StreamHandler($logPath.$serverProperties->getServerName().'.log', $loggerLevel));
        $handler = new StreamHandler($logPath.'log_'.strtolower($loggerLevelName).'.log', $loggerLevel);
        $handler->getFormatter()->allowInlineLineBreaks();
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @Bean()
     */
    public function packer(Reader $annotationReader): PackerInterface
    {
        return new Packer(new TarsTypeFactory($annotationReader));
    }

    /**
     * @Bean()
     */
    public function requestIdGenerator(): RequestIdGeneratorInterface
    {
        return new RequestIdGenerator();
    }

    /**
     * @Bean()
     */
    public function errorHandler(): ErrorHandlerInterface
    {
        return new DefaultErrorHandler();
    }

    /**
     * @Bean("registryCache")
     */
    public function cache(): CacheInterface
    {
        return new SwooleTableRegistryCache();
    }

    /**
     * @Bean("staticConnectionFactory")
     */
    public function staticConnectionFactory(ClientProperties $clientProperties): ConnectionFactory
    {
        $factory = new ConnectionFactory();
        $factory->addRoute($clientProperties->getLocator());

        return $factory;
    }

    /**
     * @Bean()
     * @Inject({"connectionFactory" = "staticConnectionFactory"})
     */
    public function queryFClient(
        ConnectionFactoryInterface $connectionFactory, PackerInterface $packer, RequestFactoryInterface $requestFactory,
        MethodMetadataFactoryInterface $methodMetadataFactory, ErrorHandlerInterface $errorHandler): QueryFClient
    {
        return new QueryFClient($connectionFactory, $packer, $requestFactory, $methodMetadataFactory, $errorHandler);
    }

    /**
     * @Bean()
     * @Inject({"staticConnectionFactory" = "staticConnectionFactory", "cache"="registryCache"})
     */
    public function connectionFactory(ConnectionFactoryInterface $staticConnectionFactory, QueryFClient $queryFClient, CacheInterface $cache): ConnectionFactoryInterface
    {
        $registryFactory = new RegistryConnectionFactory($queryFClient, $cache);

        return new ConnectionFactoryChain([$staticConnectionFactory, $registryFactory]);
    }
}
