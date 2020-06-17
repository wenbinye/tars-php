<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use function DI\autowire;
use function DI\factory;
use function DI\get;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use kuiper\di\ContainerBuilderAwareTrait;
use kuiper\di\DefinitionConfiguration;
use kuiper\logger\LoggerFactoryInterface;
use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\client\LogServant;
use wenbinye\tars\client\PropertyFServant;
use wenbinye\tars\client\QueryFServant;
use wenbinye\tars\client\ServerFServant;
use wenbinye\tars\client\StatFServant;
use wenbinye\tars\rpc\connection\ConnectionFactory;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\DefaultErrorHandler;
use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\lb\Algorithm;
use wenbinye\tars\rpc\lb\RoundRobin;
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
use wenbinye\tars\server\ServerProperties;

/**
 * @Configuration()
 */
class ClientConfiguration implements DefinitionConfiguration
{
    use ContainerBuilderAwareTrait;

    private const TARS_SERVANT_LIST = [
        LogServant::class,
        StatFServant::class,
        ServerFServant::class,
        PropertyFServant::class,
        ConfigServant::class,
    ];

    public function getDefinitions(): array
    {
        $definitions = [
            'tarsRegistryCache' => autowire(SwooleTableRegistryCache::class),
            RouteResolverInterface::class => autowire(ChainRouteResolver::class)
                ->constructor([
                    get(InMemoryRouteResolver::class),
                    get(RegistryRouteResolver::class),
                ]),
            RegistryRouteResolver::class => autowire()
                ->constructorParameter(1, get('tarsRegistryCache')),
            ServerAddressHolderFactoryInterface::class => autowire(ServerAddressHolderFactory::class)
                ->constructorParameter(1, RoundRobin::class),
            ConnectionFactoryInterface::class => autowire(ConnectionFactory::class),

            ServantProxyGeneratorInterface::class => autowire(ServantProxyGenerator::class),
            ErrorHandlerInterface::class => autowire(DefaultErrorHandler::class),
            TarsClientFactoryInterface::class => autowire(TarsClientFactory::class),
            TarsClientInterface::class => autowire(TarsClient::class),

            RequestFactoryInterface::class => autowire(RequestFactory::class),
            ResponseFactoryInterface::class => autowire(ResponseFactory::class),
            RequestIdGeneratorInterface::class => autowire(RequestIdGenerator::class),
        ];

        foreach (self::TARS_SERVANT_LIST as $clientClass) {
            $definitions[$clientClass] = factory(static function (TarsClientFactoryInterface $factory) use ($clientClass) {
                return $factory->create($clientClass);
            });
        }

        return $definitions;
    }

    /**
     * @Bean
     */
    public function inMemoryRouteResolver(
        ClientProperties $clientProperties,
        ServerProperties $serverProperties): InMemoryRouteResolver
    {
        $routeResolver = new InMemoryRouteResolver();
        if ($clientProperties->getLocator()) {
            $routeResolver->addRoute($clientProperties->getLocator());
        }
        if ($serverProperties->getNode()) {
            $routeResolver->addRoute($serverProperties->getNode());
        }

        return $routeResolver;
    }

    /**
     * @Bean()
     */
    public function queryFClient(
        InMemoryRouteResolver $inMemoryRouteResolver,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        ErrorHandlerInterface $errorHandler,
        ServantProxyGeneratorInterface $proxyGenerator,
        LoggerFactoryInterface $loggerFactory): QueryFServant
    {
        $lb = Config::getInstance()->getString('tars.application.client.load_balance', Algorithm::ROUND_ROBIN);
        $logger = $loggerFactory->create(QueryFServant::class);
        $addressHolderFactory = new ServerAddressHolderFactory($inMemoryRouteResolver, $lb, $logger);
        $connectionFactory = new ConnectionFactory($addressHolderFactory, $logger);
        $client = new TarsClient($connectionFactory, $requestFactory, $responseFactory, $logger, $errorHandler);

        return (new TarsClientFactory($client, $proxyGenerator))->create(QueryFServant::class);
    }
}
