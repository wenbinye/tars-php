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
use kuiper\di\AwareInjection;
use kuiper\di\ContainerBuilderAwareTrait;
use kuiper\di\DefinitionConfiguration;
use kuiper\di\PropertiesDefinitionSource;
use kuiper\helper\PropertyResolverInterface;
use kuiper\swoole\http\DiactorosServerRequestFactory;
use kuiper\swoole\http\ResponseSender;
use kuiper\swoole\http\ResponseSenderInterface;
use kuiper\swoole\http\ServerRequestFactoryInterface;
use kuiper\swoole\listener\ManagerStartEventListener;
use kuiper\swoole\listener\StartEventListener;
use kuiper\swoole\listener\TaskEventListener;
use kuiper\swoole\listener\WorkerStartEventListener;
use kuiper\swoole\ServerConfig;
use kuiper\swoole\ServerInterface;
use kuiper\swoole\ServerPort;
use kuiper\swoole\ServerType;
use kuiper\swoole\SwooleServer;
use kuiper\swoole\task\DispatcherInterface;
use kuiper\swoole\task\Queue;
use kuiper\swoole\task\QueueInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\client\LogServant;
use wenbinye\tars\client\PropertyFServant;
use wenbinye\tars\client\QueryFServant;
use wenbinye\tars\client\ServerFServant;
use wenbinye\tars\client\StatFServant;
use wenbinye\tars\exception\ValidationException;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\connection\ConnectionFactory;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\DefaultErrorHandler;
use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\lb\RoundRobin;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\message\RequestFactory;
use wenbinye\tars\rpc\message\RequestFactoryInterface;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\RequestIdGeneratorInterface;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\middleware\RequestLogMiddleware;
use wenbinye\tars\rpc\route\ChainRouteResolver;
use wenbinye\tars\rpc\route\InMemoryRouteResolver;
use wenbinye\tars\rpc\route\RegistryRouteResolver;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\rpc\route\ServerAddressHolderFactory;
use wenbinye\tars\rpc\route\ServerAddressHolderFactoryInterface;
use wenbinye\tars\rpc\route\SwooleTableRegistryCache;
use wenbinye\tars\rpc\ServantProxyGenerator;
use wenbinye\tars\rpc\ServantProxyGeneratorInterface;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\TarsClientFactory;
use wenbinye\tars\rpc\TarsClientFactoryInterface;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\listener\BeforeStartEventListener;
use wenbinye\tars\server\listener\TarsTcpReceiveEventListener;
use wenbinye\tars\server\listener\WorkerKeepAlive;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequestFactory;
use wenbinye\tars\server\rpc\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\server\rpc\TarsRequestHandler;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\collector\SystemCpuCollector;
use wenbinye\tars\stat\Monitor;
use wenbinye\tars\stat\MonitorInterface;
use wenbinye\tars\stat\Stat;
use wenbinye\tars\stat\StatInterface;
use wenbinye\tars\stat\StatMiddleware;
use wenbinye\tars\stat\StatStoreAdapter;
use wenbinye\tars\stat\SwooleTableStatStore;

class ServerConfiguration implements DefinitionConfiguration
{
    use ContainerBuilderAwareTrait;

    public function getDefinitions(): array
    {
        Config::getInstance()->merge([
            'application' => [
                'monitor' => [
                    'collectors' => [
                        SystemCpuCollector::class,
                    ],
                ],
                'middleware' => [
                    'client' => [
                        StatMiddleware::class,
                        RequestLogMiddleware::class,
                    ],
                    'server' => [
                        RequestLogMiddleware::class,
                    ],
                ],
                'listeners' => [
                    StartEventListener::class,
                    ManagerStartEventListener::class,
                    WorkerStartEventListener::class,
                    TaskEventListener::class,
                    TarsTcpReceiveEventListener::class,
                    WorkerKeepAlive::class,
                ],
            ],
        ]);
        $this->containerBuilder->addAwareInjection(AwareInjection::create(LoggerAwareInterface::class));
        $this->containerBuilder->addDefinitions(new PropertiesDefinitionSource(Config::getInstance()));

        $definitions = [
            Config::class => value(Config::getInstance()),
            PropertyResolverInterface::class => get(Config::class),
            AnnotationReaderInterface::class => factory([AnnotationReader::class, 'getInstance']),

            ServerInterface::class => autowire(SwooleServer::class),
            SwooleServer::class => get(ServerInterface::class),
            QueueInterface::class => autowire(Queue::class),
            DispatcherInterface::class => get(QueueInterface::class),

            StatInterface::class => autowire(Stat::class),
            StatStoreAdapter::class => autowire(SwooleTableStatStore::class),

            ServerRequestFactoryInterface::class => autowire(DiactorosServerRequestFactory::class),
            RequestFactoryInterface::class => autowire(RequestFactory::class),
            ResponseFactoryInterface::class => autowire(ResponseFactory::class),
            TarsServerRequestFactoryInterface::class => autowire(ServerRequestFactory::class),
            RequestIdGeneratorInterface::class => autowire(RequestIdGenerator::class),
            RequestHandlerInterface::class => autowire(TarsRequestHandler::class),

            ServantProxyGeneratorInterface::class => autowire(ServantProxyGenerator::class),
            MethodMetadataFactoryInterface::class => autowire(MethodMetadataFactory::class),
            ErrorHandlerInterface::class => autowire(DefaultErrorHandler::class),
            TarsClientFactoryInterface::class => autowire(TarsClientFactory::class),
            TarsClientInterface::class => autowire(TarsClient::class),
            ConnectionFactoryInterface::class => autowire(ConnectionFactory::class),
            'registryCache' => autowire(SwooleTableRegistryCache::class),
            RouteResolverInterface::class => autowire(ChainRouteResolver::class)
                ->constructorParameter('resolvers', [
                    get(InMemoryRouteResolver::class),
                    get(RegistryRouteResolver::class),
                ]),
            RegistryRouteResolver::class => autowire()
                ->constructorParameter('cache', get('registryCache')),
            ServerAddressHolderFactoryInterface::class => autowire(ServerAddressHolderFactory::class)
                ->constructorParameter('loadBalanceAlgorithm', RoundRobin::class),

            ResponseSenderInterface::class => autowire(ResponseSender::class),
        ];
        foreach ([LogServant::class, StatFServant::class, ServerFServant::class, PropertyFServant::class, ConfigServant::class] as $clientClass) {
            $definitions[$clientClass] = factory([TarsClientFactoryInterface::class, 'create'])
                ->parameter('clientClassName', $clientClass);
        }

        return $definitions;
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
    public function eventDispatcher(BeforeStartEventListener $listener): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        $listener->setEventDispatcher($dispatcher);
        $dispatcher->addListener($listener->getSubscribedEvent(), $listener);

        return $dispatcher;
    }

    /**
     * @Bean()
     *
     * @throws ValidationException
     */
    public function serverProperties(PropertyLoader $propertyLoader, Config $config): ServerProperties
    {
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $configFile = $serverProperties->getSourcePath().'/config.php';
        if (file_exists($configFile)) {
            $config->merge(require $configFile);
        }

        return $serverProperties;
    }

    /**
     * @Bean()
     *
     * @throws ValidationException
     */
    public function clientProperties(PropertyLoader $propertyLoader, Config $config): ClientProperties
    {
        return $propertyLoader->loadClientProperties($config);
    }

    /**
     * @Bean()
     */
    public function serverConfig(ServerProperties $serverProperties): ServerConfig
    {
        $ports = [];
        foreach ($serverProperties->getAdapters() as $adapter) {
            $ports[] = new ServerPort($adapter->getEndpoint()->getHost(), $adapter->getEndpoint()->getPort(),
                ServerType::fromValue($adapter->getSwooleServerType()));
        }

        $serverConfig = new ServerConfig($serverProperties->getServerName(), $serverProperties->getSwooleSettings(), $ports);
        $serverConfig->setMasterPidFile($serverProperties->getDataPath().'/master.pid');
        $serverConfig->setManagerPidFile($serverProperties->getDataPath().'/manager.pid');

        return $serverConfig;
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
        $handler->getFormatter()->allowInlineLineBreaks();
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

    /**
     * @Bean
     */
    public function inMemoryRouteResolver(ClientProperties $clientProperties, ServerProperties $serverProperties): InMemoryRouteResolver
    {
        $factory = new InMemoryRouteResolver();
        $factory->addRoute($clientProperties->getLocator());
        $factory->addRoute($serverProperties->getNode());

        return $factory;
    }

    /**
     * @Bean()
     */
    public function queryFClient(InMemoryRouteResolver $routeResolver, RequestFactoryInterface $requestFactory,
                                 ResponseFactoryInterface $responseFactory, ErrorHandlerInterface $errorHandler,
                                 ServantProxyGeneratorInterface $proxyGenerator, LoggerInterface $logger): QueryFServant
    {
        $connectionFactory = new ConnectionFactory(new ServerAddressHolderFactory($routeResolver));
        $connectionFactory->setLogger($logger);
        $client = new TarsClient($connectionFactory, $requestFactory, $responseFactory, $errorHandler);

        return (new TarsClientFactory($client, $proxyGenerator))->create(QueryFServant::class);
    }

    /**
     * @Bean()
     */
    public function monitor(ContainerInterface $container, Config $config, ServerProperties $serverProperties, PropertyFServant $propertyFClient, LoggerInterface $logger): MonitorInterface
    {
        $collectors = [];
        foreach ($config->get('application.monitor.collectors', []) as $collector) {
            $collectors[] = $container->get($collector);
        }

        $monitor = new Monitor($serverProperties, $propertyFClient, $collectors);
        $monitor->setLogger($logger);

        return $monitor;
    }
}
