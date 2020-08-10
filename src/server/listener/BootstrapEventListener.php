<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\di\ComponentCollection;
use kuiper\event\annotation\EventListener;
use kuiper\event\EventListenerInterface;
use kuiper\swoole\constants\ServerType;
use kuiper\swoole\event\ReceiveEvent;
use kuiper\swoole\event\RequestEvent;
use kuiper\swoole\listener\HttpRequestEventListener;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\message\ServerRequestFactory as TarsServerRequestFactory;
use wenbinye\tars\rpc\message\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\rpc\server\RequestHandlerInterface;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\BootstrapEvent;
use wenbinye\tars\server\Protocol;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        $config = Config::getInstance();
        $this->addTarsClientMiddleware($config->get('application.tars.middleware.client', []));
        $this->registerServants($config->get('application.tars.servants', []));
        $this->addTarsServantMiddleware($config->get('application.tars.middleware.servant', []));
        $this->addEventListeners($event);
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
            $this->logger->info(static::TAG.'enable client middlewares '.implode(',', $middlewares));
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
            foreach (ComponentCollection::getAnnotations(TarsServant::class) as $annotation) {
                /* @var TarsServant $annotation */
                $servants[$annotation->name] = $annotation->getComponentId();
            }
            foreach ($servants as $servantName => $servantComponentId) {
                $servantName = $this->normalizeServantName($servantName, $serverProperties);
                $this->logger->info(static::TAG.'register servant', [
                    'servant' => $servantName,
                    'service' => $servantComponentId,
                ]);
                $serverRequestFactory->register($servantName, $servantComponentId);
            }
        }
    }

    private function addTarsServantMiddleware(array $middlewares): void
    {
        if (!empty($middlewares)) {
            $this->logger->info(static::TAG.'enable server middlewares '.implode(',', $middlewares));

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

    private function addEventListeners(BootstrapEvent $event): void
    {
        $config = Config::getInstance();
        $events = [];
        foreach ($config->get('application.listeners', []) as $eventName => $listenerId) {
            $events[] = $this->attach($event, $listenerId, is_string($eventName) ? $eventName : null);
        }
        /** @var EventListener $annotation */
        foreach (ComponentCollection::getAnnotations(EventListener::class) as $annotation) {
            $listener = $this->container->get($annotation->getComponentId());
            if (!($listener instanceof EventListenerInterface)) {
                throw new \InvalidArgumentException($annotation->getTarget()->getName().' should implements '.EventListenerInterface::class);
            }
            $events[] = $this->attach($event, $annotation->getComponentId(), $annotation->value);
        }

        $serverProperties = $this->container->get(ServerProperties::class);
        $serverType = ServerType::fromValue($serverProperties->getPrimaryAdapter()->getServerType());
        if ($serverType->isHttpProtocol() && !in_array(RequestEvent::class, $events, true)) {
            $this->attach($event, HttpRequestEventListener::class);
        }
        if (!in_array(ReceiveEvent::class, $events, true)) {
            foreach ($serverProperties->getAdapters() as $adapter) {
                if (Protocol::TARS === $adapter->getProtocol()) {
                    $this->attach($event, TarsTcpReceiveEventListener::class);
                    break;
                }
            }
        }
    }

    /**
     * @param BootstrapEvent $event
     * @param string         $listenerId
     * @param string         $eventName
     *
     * @return string
     */
    private function attach(BootstrapEvent $event, string $listenerId, ?string $eventName = null): string
    {
        $this->logger->debug(static::TAG."attach $listenerId");
        $listener = $this->container->get($listenerId);

        if ($listener instanceof EventListenerInterface) {
            $eventName = $listener->getSubscribedEvent();
        }
        if (is_string($eventName)) {
            if (BootstrapEvent::class === $eventName) {
                $listener($event);
            } else {
                $this->eventDispatcher->addListener($eventName, $listener);
            }

            return $eventName;
        }

        throw new \InvalidArgumentException("EventListener $listenerId does not bind to any event");
    }
}
