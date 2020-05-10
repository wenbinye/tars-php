<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use Dotenv\Dotenv;
use kuiper\di\annotation\EventListener;
use kuiper\di\ComponentCollection;
use kuiper\helper\Properties;
use kuiper\swoole\event\BootstrapEvent;
use kuiper\swoole\event\ReceiveEvent;
use kuiper\swoole\event\RequestEvent;
use kuiper\swoole\listener\EventListenerInterface;
use kuiper\swoole\listener\HttpRequestEventListener;
use kuiper\swoole\livereload\Reloader;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\Protocol;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequestFactory as TarsServerRequestFactory;
use wenbinye\tars\server\rpc\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\server\ServerProperties;

class BootstrapEventListener implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ContainerInterface $container, ?LoggerInterface $logger)
    {
        $this->container = $container;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        $config = Config::getInstance();
        $this->loadConfig($config);
        $this->addTarsClientMiddleware($config->get('application.middleware.client', []));
        $this->registerServants($config->get('application.servants', []));
        $this->addTarsServantMiddleware($config->get('application.middleware.servant', []));
        $this->addEventListeners();
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getSubscribedEvent(): string
    {
        return BootstrapEvent::class;
    }

    private function addTarsClientMiddleware(array $middlewares): void
    {
        if (!empty($middlewares)) {
            $this->logger->info(static::TAG.'enable client middlewares', ['middlewares' => $middlewares]);
            $tarsClient = $this->container->get(TarsClientInterface::class);
            foreach ($middlewares as $middlewareId) {
                $tarsClient->addMiddleware($this->container->get($middlewareId));
            }
        }
    }

    private function registerServants(array $servants): void
    {
        $serverRequestFactory = $this->container->get(TarsServerRequestFactoryInterface::class);
        $serverProperties = $this->container->get(ServerProperties::class);
        if ($serverRequestFactory instanceof TarsServerRequestFactory) {
            foreach (ComponentCollection::getComponents(TarsServant::class) as $servantInterface) {
                /** @var TarsServant $annotation */
                $annotation = ComponentCollection::getAnnotation($servantInterface, TarsServant::class);
                $servants[$annotation->name] = $servantInterface;
            }
            foreach ($servants as $servantName => $servantInterface) {
                $servantName = $this->normalizeServantName($servantName, $serverProperties);
                $this->logger->info(static::TAG.'register servant', [
                    'servant' => $servantName,
                    'service' => $servantInterface,
                ]);
                $serverRequestFactory->register($servantName, $servantInterface);
            }
        }
    }

    private function addTarsServantMiddleware(array $middlewares): void
    {
        if (!empty($middlewares)) {
            $this->logger->info(static::TAG.'enable server middlewares', ['middlewares' => $middlewares]);

            $tarsRequestHandler = $this->container->get(RequestHandlerInterface::class);
            foreach ($middlewares as $middlewareId) {
                $tarsRequestHandler->addMiddleware($this->container->get($middlewareId));
            }
        }
    }

    private function normalizeServantName(string $servantName, ServerProperties $serverProperties): string
    {
        if (false === strpos($servantName, '.')) {
            $servantName = $serverProperties->getServerName().'.'.$servantName;
        }

        return $servantName;
    }

    private function addEventListeners(): void
    {
        $config = Config::getInstance();
        $events = [];
        foreach ($config->get('application.listeners', []) as $eventName => $listenerId) {
            $events[] = $this->attach($listenerId, $eventName);
        }
        /** @var EventListener $annotation */
        foreach (ComponentCollection::getComponents(EventListener::class) as $annotation) {
            $listener = $annotation->getTarget()->getName();
            try {
                $this->attach($listener, $annotation->value);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("EventListener $listener should implements ".EventListenerInterface::class);
            }
        }
        $protocol = Protocol::fromValue($config->get('application.protocol'));
        if ($protocol->isHttpProtocol() && !in_array(RequestEvent::class, $events, true)) {
            $this->attach(HttpRequestEventListener::class);
        }
        if (Protocol::TARS === $protocol->value && !in_array(ReceiveEvent::class, $events, true)) {
            $this->attach(TarsTcpReceiveEventListener::class);
        }
        if ($config->getBool('application.livereload.enabled')) {
            $this->logger->warning(static::TAG.'livereload is enable which should disable in production');
            $this->attach(Reloader::class);
        }
    }

    /**
     * @param string $eventName
     */
    private function attach(string $listenerId, $eventName = null): string
    {
        $this->logger->debug(static::TAG."attach $listenerId");
        $listener = $this->container->get($listenerId);

        if ($listener instanceof EventListenerInterface) {
            $eventName = $listener->getSubscribedEvent();
        }
        if (is_string($eventName)) {
            $this->eventDispatcher->addListener($eventName, $listener);

            return $eventName;
        }

        throw new \InvalidArgumentException("config application.listeners $listenerId does not bind to any event");
    }

    private function loadConfig(Properties $config): void
    {
        $serverProperties = $this->container->get(ServerProperties::class);
        $env = $config->getString('tars.application.server.env_config_file');
        if ($env) {
            $configServant = $this->container->get(ConfigServant::class);
            $ret = $configServant->loadConfig($serverProperties->getApp(), $serverProperties->getServer(), $env, $content);
            if (0 === $ret) {
                file_put_contents($serverProperties->getBasePath().'/'.$env, $content);
            }
            if (class_exists(Dotenv::class)) {
                Dotenv::createImmutable($serverProperties->getBasePath(), [$env, '.env'], false)->safeLoad();
            }
        }
        $configFile = $serverProperties->getSourcePath().'/config.php';
        if (file_exists($configFile)) {
            /* @noinspection PhpIncludeInspection */
            $config->merge(require $configFile);
        }
    }
}
