<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use InvalidArgumentException;
use kuiper\annotations\AnnotationReader;
use kuiper\swoole\pool\PoolFactory;
use kuiper\swoole\pool\PoolFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheInterface;
use wenbinye\tars\client\QueryFServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\connection\ConnectionFactory;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\lb\Algorithm;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\ClientRequestFactoryInterface;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\RequestIdGeneratorInterface;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\middleware\MiddlewareInterface;
use wenbinye\tars\rpc\route\cache\SwooleTableRegistryCache;
use wenbinye\tars\rpc\route\InMemoryRouteResolver;
use wenbinye\tars\rpc\route\RegistryRouteResolver;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\rpc\route\ServerAddressHolderFactory;
use wenbinye\tars\rpc\route\ServerAddressHolderFactoryInterface;

class TarsClientBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteResolverInterface|null
     */
    private $routeResolver;

    /**
     * @var Route|null
     */
    private $locator;

    /**
     * @var QueryFServant|null
     */
    private $queryFClient;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @var PackerInterface|null
     */
    private $packer;

    /**
     * @var PoolFactoryInterface|null
     */
    private $poolFactory;

    /**
     * @var MethodMetadataFactoryInterface|null
     */
    private $methodMetadataFactory;

    /**
     * @var RequestIdGeneratorInterface|null
     */
    private $requestIdGenerator;

    /**
     * @var ServerAddressHolderFactoryInterface|null
     */
    private $serverAddressHolderFactory;

    /**
     * @var ConnectionFactoryInterface|null
     */
    private $connectionFactory;

    /**
     * @var ClientRequestFactoryInterface|null
     */
    private $requestFactory;

    /**
     * @var ResponseFactoryInterface|null
     */
    private $responseFactory;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * @var ServantProxyGeneratorInterface|null
     */
    private $servantProxyGenerator;

    public function getLocator(): Route
    {
        return $this->locator;
    }

    public function setLocator(Route $locator): TarsClientBuilder
    {
        $this->locator = $locator;

        return $this;
    }

    public function getQueryFClient(): QueryFServant
    {
        if (null === $this->queryFClient) {
            if (null === $this->locator) {
                throw new InvalidArgumentException('locator is required');
            }
            $routeResolver = new InMemoryRouteResolver();
            $routeResolver->addRoute($this->locator);
            $routeHolderFactory = new ServerAddressHolderFactory($routeResolver);
            $connectionFactory = new ConnectionFactory($this->getPoolFactory(), $routeHolderFactory, $this->logger);
            $tarsClient = new TarsClient(
                $connectionFactory,
                $this->getRequestFactory(),
                $this->getResponseFactory(),
                $this->logger,
                $this->middlewares
            );
            $proxyClass = $this->getServantProxyGenerator()->generate(QueryFServant::class);
            $this->queryFClient = new $proxyClass($tarsClient);
        }

        return $this->queryFClient;
    }

    public function setQueryFClient(QueryFServant $queryFClient): TarsClientBuilder
    {
        $this->queryFClient = $queryFClient;

        return $this;
    }

    public function getCache(): CacheInterface
    {
        if (null === $this->cache) {
            $this->cache = new SwooleTableRegistryCache();
        }

        return $this->cache;
    }

    public function setCache(CacheInterface $cache): TarsClientBuilder
    {
        $this->cache = $cache;

        return $this;
    }

    public function getPoolFactory(): PoolFactoryInterface
    {
        if (null === $this->poolFactory) {
            $this->poolFactory = new PoolFactory();
        }

        return $this->poolFactory;
    }

    public function setPoolFactory(PoolFactoryInterface $poolFactory): TarsClientBuilder
    {
        $this->poolFactory = $poolFactory;

        return $this;
    }

    public function getRouteResolver(): RouteResolverInterface
    {
        if (null === $this->routeResolver) {
            $this->routeResolver = new RegistryRouteResolver($this->getQueryFClient(), $this->getCache());
        }

        return $this->routeResolver;
    }

    /**
     * @param mixed $routeResolver
     */
    public function setRouteResolver($routeResolver): TarsClientBuilder
    {
        $this->routeResolver = $routeResolver;

        return $this;
    }

    public function getPacker(): PackerInterface
    {
        if (null === $this->packer) {
            $this->packer = new Packer(AnnotationReader::getInstance());
        }

        return $this->packer;
    }

    public function setPacker(PackerInterface $packer): TarsClientBuilder
    {
        $this->packer = $packer;

        return $this;
    }

    public function getMethodMetadataFactory(): MethodMetadataFactoryInterface
    {
        if (null === $this->methodMetadataFactory) {
            $this->methodMetadataFactory = new MethodMetadataFactory(AnnotationReader::getInstance());
        }

        return $this->methodMetadataFactory;
    }

    public function setMethodMetadataFactory(MethodMetadataFactoryInterface $methodMetadataFactory): TarsClientBuilder
    {
        $this->methodMetadataFactory = $methodMetadataFactory;

        return $this;
    }

    public function getRequestIdGenerator(): RequestIdGeneratorInterface
    {
        if (null === $this->requestIdGenerator) {
            $this->requestIdGenerator = new RequestIdGenerator();
        }

        return $this->requestIdGenerator;
    }

    public function setRequestIdGenerator(RequestIdGeneratorInterface $requestIdGenerator): TarsClientBuilder
    {
        $this->requestIdGenerator = $requestIdGenerator;

        return $this;
    }

    public function getServerAddressHolderFactory(): ServerAddressHolderFactoryInterface
    {
        if (null === $this->serverAddressHolderFactory) {
            $this->serverAddressHolderFactory = new ServerAddressHolderFactory($this->getRouteResolver(), Algorithm::ROUND_ROBIN, $this->logger);
        }

        return $this->serverAddressHolderFactory;
    }

    public function setServerAddressHolderFactory(ServerAddressHolderFactoryInterface $serverAddressHolderFactory): TarsClientBuilder
    {
        $this->serverAddressHolderFactory = $serverAddressHolderFactory;

        return $this;
    }

    public function getServantProxyGenerator(): ServantProxyGeneratorInterface
    {
        if (null === $this->servantProxyGenerator) {
            $this->servantProxyGenerator = new ServantProxyGenerator(AnnotationReader::getInstance());
        }

        return $this->servantProxyGenerator;
    }

    public function setServantProxyGenerator(ServantProxyGeneratorInterface $servantProxyGenerator): TarsClientBuilder
    {
        $this->servantProxyGenerator = $servantProxyGenerator;

        return $this;
    }

    public function getConnectionFactory(): ConnectionFactoryInterface
    {
        if (null === $this->connectionFactory) {
            $this->connectionFactory = new ConnectionFactory($this->getPoolFactory(), $this->getServerAddressHolderFactory(), $this->logger);
        }

        return $this->connectionFactory;
    }

    public function setConnectionFactory(ConnectionFactoryInterface $connectionFactory): TarsClientBuilder
    {
        $this->connectionFactory = $connectionFactory;

        return $this;
    }

    public function getRequestFactory(): ClientRequestFactoryInterface
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = new ClientRequestFactory(
                $this->getMethodMetadataFactory(),
                $this->getPacker(),
                $this->getRequestIdGenerator()
            );
        }

        return $this->requestFactory;
    }

    public function setRequestFactory(ClientRequestFactoryInterface $requestFactory): TarsClientBuilder
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        if (null === $this->responseFactory) {
            $this->responseFactory = new ResponseFactory($this->getPacker());
        }

        return $this->responseFactory;
    }

    public function setResponseFactory(ResponseFactoryInterface $responseFactory): TarsClientBuilder
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    public function addMiddleware(MiddlewareInterface $middleware): TarsClientBuilder
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function setMiddlewares(array $middlewares): TarsClientBuilder
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    public function build(?string $clientClass = null): TarsClientInterface
    {
        if (!isset($clientClass)) {
            $clientClass = TarsClient::class;
        }

        return new $clientClass(
            $this->getConnectionFactory(),
            $this->getRequestFactory(),
            $this->getResponseFactory(),
            $this->logger,
            $this->middlewares
        );
    }

    /**
     * @param string      $clientClassName
     * @param string|null $servantName
     *
     * @return object
     */
    public function createProxy(string $clientClassName, ?string $servantName = null)
    {
        $proxyClass = $this->getServantProxyGenerator()->generate($clientClassName, $servantName);

        return new $proxyClass($this->build());
    }
}
