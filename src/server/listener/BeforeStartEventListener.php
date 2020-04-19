<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\di\ComponentCollection;
use kuiper\event\EventDispatcherAwareTrait;
use kuiper\swoole\event\BeforeStartEvent;
use kuiper\swoole\listener\EventListenerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequestFactory as TarsServerRequestFactory;
use wenbinye\tars\server\rpc\ServerRequestFactoryInterface as TarsServerRequestFactoryInterface;
use wenbinye\tars\server\ServerProperties;

class BeforeStartEventListener implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use EventDispatcherAwareTrait;
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $this->addTarsClientMiddleware((array) $config->get('application.middleware.client', []));
        $this->registerServants((array) $config->get('application.servants', []));
        $this->addTarsServantMiddleware((array) $config->get('application.middleware.server', []));

        foreach ($config->get('application.listeners', []) as $eventName => $listenerId) {
            $this->logger->debug("[BeforeStartEventListener] attach $listenerId");
            $listener = $this->container->get($listenerId);
            if ($listener instanceof EventListenerInterface) {
                $this->eventDispatcher->addListener($listener->getSubscribedEvent(), $listener);
            } elseif (is_string($eventName)) {
                $this->eventDispatcher->addListener($eventName, $listener);
            } else {
                throw new \InvalidArgumentException("config application.listeners $listenerId does not bind to any event");
            }
        }
    }

    private function addTarsClientMiddleware(array $middlewares): void
    {
        if (!empty($middlewares)) {
            $this->logger->info('[BeforeStartEventListener] enable client middlewares', ['middlewares' => $middlewares]);
            $tarsClient = $this->container->get(TarsClientInterface::class);
            foreach ($middlewares as $middlewareId) {
                $tarsClient->addMiddleware($this->container->get($middlewareId));
            }
        }
    }

    private function registerServants(array $servants)
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
                $this->logger->info('[BeforeStartEventListener] register servant', [
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
            $this->logger->info('[BeforeStartEventListener] enable server middlewares', ['middlewares' => $middlewares]);

            $tarsRequestHandler = $this->container->get(RequestHandlerInterface::class);
            foreach ($middlewares as $middlewareId) {
                $tarsRequestHandler->addMiddleware($this->container->get($middlewareId));
            }
        }
    }

    public function getSubscribedEvent(): string
    {
        return BeforeStartEvent::class;
    }

    private function normalizeServantName(string $servantName, ServerProperties $serverProperties)
    {
        if (false === strpos($servantName, '.')) {
            $servantName = $serverProperties->getServerName().'.'.$servantName;
        }

        return $servantName;
    }
}
