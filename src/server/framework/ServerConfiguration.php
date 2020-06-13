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
use kuiper\swoole\http\HttpMessageFactoryHolder;
use kuiper\swoole\http\SwooleRequestBridgeInterface;
use kuiper\swoole\http\SwooleResponseBridge;
use kuiper\swoole\http\SwooleResponseBridgeInterface;
use kuiper\swoole\monolog\CoroutineIdProcessor;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerConfig;
use kuiper\swoole\ServerFactory;
use kuiper\swoole\ServerPort;
use kuiper\web\middleware\AccessLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use wenbinye\tars\client\PropertyFServant;
use wenbinye\tars\rpc\message\ServerRequestFactory;
use wenbinye\tars\rpc\message\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\rpc\middleware\ServerRequestLog;
use wenbinye\tars\rpc\server\RequestHandlerInterface;
use wenbinye\tars\rpc\server\TarsRequestHandler;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\stat\Monitor;
use wenbinye\tars\stat\MonitorInterface;
use wenbinye\tars\stat\Stat;
use wenbinye\tars\stat\StatInterface;
use wenbinye\tars\stat\StatStoreAdapter;
use wenbinye\tars\stat\SwooleTableStatStore;

/**
 * @Configuration()
 */
class ServerConfiguration implements DefinitionConfiguration
{
    use ContainerBuilderAwareTrait;

    public function getDefinitions(): array
    {
        return [
            ServerInterface::class => factory([ServerFactory::class, 'create']),
            StatInterface::class => autowire(Stat::class),
            StatStoreAdapter::class => autowire(SwooleTableStatStore::class),
            TarsServerRequestFactoryInterface::class => autowire(ServerRequestFactory::class),
            RequestHandlerInterface::class => autowire(TarsRequestHandler::class),
            SwooleResponseBridgeInterface::class => autowire(SwooleResponseBridge::class),
            ServerRequestLog::class => autowire()
                ->method('setLogger', get('accessLogger')),
            AccessLog::class => autowire()
                ->method('setLogger', get('accessLogger')),
        ];
    }

    /**
     * @Bean()
     */
    public function serverFactory(
        ContainerInterface $container,
        EventDispatcherInterface $eventDispatcher,
        LoggerFactoryInterface $loggerFactory): ServerFactory
    {
        $config = Config::getInstance();
        $serverFactory = new ServerFactory($loggerFactory->create(ServerFactory::class));
        $serverFactory->setEventDispatcher($eventDispatcher);
        $serverFactory->enablePhpServer($config->getBool('tars.application.server.enable_php_server'));
        if ($config->get('application.http_protocol')) {
            $serverFactory->setHttpMessageFactoryHolder($container->get(HttpMessageFactoryHolder::class));
            $serverFactory->setSwooleRequestBridge($container->get(SwooleRequestBridgeInterface::class));
            $serverFactory->setSwooleResponseBridge($container->get(SwooleResponseBridgeInterface::class));
        }

        return $serverFactory;
    }

    /**
     * @Bean()
     */
    public function serverConfig(ServerProperties $serverProperties): ServerConfig
    {
        $ports = [];
        foreach ($serverProperties->getAdapters() as $adapter) {
            $port = $adapter->getEndpoint()->getPort();
            if (isset($ports[$port])) {
                continue;
            }
            $ports[$port] = new ServerPort($adapter->getEndpoint()->getHost(), $port, $adapter->getServerType());
        }

        $serverConfig = new ServerConfig($serverProperties->getServerName(), $serverProperties->getServerSettings(), array_values($ports));
        $serverConfig->setMasterPidFile($serverProperties->getDataPath().'/master.pid');

        return $serverConfig;
    }

    /**
     * @Bean()
     */
    public function monitor(
        ContainerInterface $container,
        ServerProperties $serverProperties,
        PropertyFServant $propertyFClient,
        LoggerFactoryInterface $loggerFactory): MonitorInterface
    {
        $collectors = [];
        foreach (Config::getInstance()->get('application.monitor.collectors', []) as $collector) {
            $collectors[] = $container->get($collector);
        }

        return new Monitor($serverProperties, $propertyFClient, $collectors, $loggerFactory->create(Monitor::class));
    }

    /**
     * @Bean()
     */
    public function accessLogger(ServerProperties $serverProperties)
    {
        $logger = new Logger($serverProperties->getServerName());
        $logFile = $serverProperties->getAppLogPath().'/access.log';
        $handler = new StreamHandler($logFile, Logger::INFO);
        $logger->pushHandler($handler);
        $handler->setFormatter(new LineFormatter("%message% %extra%\n"));
        $logger->pushProcessor(new CoroutineIdProcessor());

        return $logger;
    }
}
