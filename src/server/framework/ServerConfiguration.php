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
use kuiper\swoole\event\BeforeStartEvent;
use kuiper\swoole\http\ResponseSender;
use kuiper\swoole\http\ResponseSenderInterface;
use kuiper\swoole\http\ServerRequestFactoryInterface;
use kuiper\swoole\http\ZendDiactorosServerRequestFactory;
use kuiper\swoole\listener\EventListenerInterface;
use kuiper\swoole\listener\HttpRequestEventListener;
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
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\log\LogServant;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\registry\QueryFServant;
use wenbinye\tars\rpc\connection\ConnectionFactory;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\DefaultErrorHandler;
use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\message\RequestFactory;
use wenbinye\tars\rpc\message\RequestFactoryInterface;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\RequestIdGeneratorInterface;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\route\ChainRouteResolver;
use wenbinye\tars\rpc\route\InMemoryRouteResolver;
use wenbinye\tars\rpc\route\RegistryRouteResolver;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\rpc\route\SwooleTableRegistryCache;
use wenbinye\tars\rpc\ServantProxyGenerator;
use wenbinye\tars\rpc\ServantProxyGeneratorInterface;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\TarsClientFactory;
use wenbinye\tars\rpc\TarsClientFactoryInterface;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
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
use wenbinye\tars\stat\PropertyFServant;
use wenbinye\tars\stat\ServerFServant;
use wenbinye\tars\stat\Stat;
use wenbinye\tars\stat\StatFServant;
use wenbinye\tars\stat\StatInterface;
use wenbinye\tars\stat\StatMiddleware;
use wenbinye\tars\stat\StatStoreAdapter;
use wenbinye\tars\stat\SwooleTableStatStore;
use wenbinye\tars\support\loadBalance\RoundRobin;

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
                    ],
                ],
                'listeners' => [
                    StartEventListener::class,
                    ManagerStartEventListener::class,
                    WorkerStartEventListener::class,
                    TaskEventListener::class,
                    HttpRequestEventListener::class,
                    TarsTcpReceiveEventListener::class,
                    WorkerKeepAlive::class,
                ],
            ],
        ]);
        $this->containerBuilder->addAwareInjection(AwareInjection::create(LoggerAwareInterface::class));
        $this->containerBuilder->addDefinitions(new PropertiesDefinitionSource(Config::getInstance()));

        $definitions = [
            Config::class => value(Config::getInstance()),
            AnnotationReaderInterface::class => factory([AnnotationReader::class, 'getInstance']),

            ServerInterface::class => autowire(SwooleServer::class),
            SwooleServer::class => get(ServerInterface::class),
            QueueInterface::class => autowire(Queue::class),
            DispatcherInterface::class => get(QueueInterface::class),

            StatInterface::class => autowire(Stat::class),
            StatStoreAdapter::class => autowire(SwooleTableStatStore::class),

            ServerRequestFactoryInterface::class => autowire(ZendDiactorosServerRequestFactory::class),
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
            'registryCache' => autowire(SwooleTableRegistryCache::class),
            RouteResolverInterface::class => autowire(ChainRouteResolver::class)
                ->constructorParameter('resolvers', [
                    get(InMemoryRouteResolver::class),
                    get(RegistryRouteResolver::class),
                ]),
            RegistryRouteResolver::class => autowire()
                ->constructorParameter('cache', get('registryCache')),
            ConnectionFactoryInterface::class => autowire(ConnectionFactory::class)
                ->constructorParameter('loadBalanceAlgorithm', RoundRobin::class),

            ResponseSenderInterface::class => autowire(ResponseSender::class),
        ];
        foreach ([LogServant::class, StatFServant::class, ServerFServant::class, PropertyFServant::class] as $clientClass) {
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
    public function eventDispatcher(ContainerInterface $container, Config $config, LoggerInterface $logger): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(BeforeStartEvent::class, function () use ($container, $dispatcher, $config, $logger) {
            $this->addTarsClientMiddleware($container, $config);
            $this->addTarsServantMiddleware($container, $config);

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

    private function addTarsClientMiddleware(ContainerInterface $container, Config $config): void
    {
        $middlewares = $config->get('application.middleware.client', []);
        if (!empty($middlewares)) {
            $tarsClient = $container->get(TarsClient::class);
            foreach ($middlewares as $middlewareId) {
                $tarsClient->addMiddleware($container->get($middlewareId));
            }
        }
    }

    private function addTarsServantMiddleware(ContainerInterface $container, Config $config): void
    {
        foreach ($config->get('application.servants', []) as $servantName => $servantInterface) {
            TarsServant::register($servantName, $servantInterface);
        }

        $middlewares = $config->get('application.middleware.servant', []);
        if (!empty($middlewares)) {
            $tarsRequestHandler = $container->get(TarsRequestHandler::class);
            foreach ($middlewares as $middlewareId) {
                $tarsRequestHandler->addMiddleware($container->get($middlewareId));
            }
        }
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
    public function packer(AnnotationReaderInterface $annotationReader): PackerInterface
    {
        return new Packer(new TarsTypeFactory($annotationReader));
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
                                 ServantProxyGeneratorInterface $proxyGenerator): QueryFServant
    {
        $client = new TarsClient(new ConnectionFactory($routeResolver), $requestFactory, $responseFactory, $errorHandler);

        return (new TarsClientFactory($client, $proxyGenerator))->create(QueryFServant::class);
    }

    /**
     * @Bean()
     */
    public function monitor(ContainerInterface $container, Config $config, ServerProperties $serverProperties, PropertyFServant $propertyFClient): MonitorInterface
    {
        $collectors = [];
        foreach ($config->get('application.monitor.collectors', []) as $collector) {
            $collectors[] = $container->get($collector);
        }

        return new Monitor($serverProperties, $propertyFClient, $collectors);
    }
}
