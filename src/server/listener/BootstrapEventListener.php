<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\di\ComponentCollection;
use kuiper\event\annotation\EventListener;
use kuiper\event\EventListenerInterface;
use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\event\BootstrapEvent;
use kuiper\swoole\event\ReceiveEvent;
use kuiper\swoole\event\RequestEvent;
use kuiper\swoole\listener\HttpRequestEventListener;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\message\ServerRequestFactory as TarsServerRequestFactory;
use wenbinye\tars\rpc\message\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\rpc\server\RequestHandlerInterface;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
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
        if ($config->getBool('application.enable_coroutine', true)) {
            Coroutine::enable();
        }
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

    private function addEventListeners(): void
    {
        $config = Config::getInstance();
        $events = [];
        foreach ($config->get('application.listeners', []) as $eventName => $listenerId) {
            $events[] = $this->attach($listenerId, is_string($eventName) ? $eventName : null);
        }
        /** @var EventListener $annotation */
        foreach (ComponentCollection::getAnnotations(EventListener::class) as $annotation) {
            try {
                $this->attach($annotation->getComponentId(), $annotation->value);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException('EventListener should implements '.EventListenerInterface::class);
            }
        }
        $protocol = Protocol::fromValue($config->get('application.protocol'));
        if ($protocol->isHttpProtocol() && !in_array(RequestEvent::class, $events, true)) {
            $this->attach(HttpRequestEventListener::class);
        }
        if (Protocol::TARS === $protocol->value && !in_array(ReceiveEvent::class, $events, true)) {
            $this->attach(TarsTcpReceiveEventListener::class);
        }
    }

    /**
     * @param string $eventName
     */
    private function attach(string $listenerId, ?string $eventName = null): string
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
}
