<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use kuiper\swoole\event\ReceiveEvent;
use kuiper\swoole\listener\EventListenerInterface;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequestFactoryInterface;

class TarsRpc implements EventListenerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;
    /**
     * @var ServerRequestFactoryInterface
     */
    private $serverRequestFactory;

    /**
     * TarsRequestHandler constructor.
     */
    public function __construct(ServerRequestFactoryInterface $serverRequestFactory, RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->serverRequestFactory = $serverRequestFactory;
    }

    /**
     * @param ReceiveEvent $event
     */
    public function __invoke($event): void
    {
        $response = $this->requestHandler->handle($this->serverRequestFactory->create($event->getData()));
        $event->getSwooleServer()->send($event->getFd(), $response->getBody());
    }

    public function getSubscribedEvent(): string
    {
        return ReceiveEvent::class;
    }
}
