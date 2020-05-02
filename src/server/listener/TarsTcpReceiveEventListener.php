<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\swoole\event\ReceiveEvent;
use kuiper\swoole\listener\EventListenerInterface;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequestFactoryInterface;

class TarsTcpReceiveEventListener implements EventListenerInterface
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
        // TODO: 会不会有数据包不完整情况？
        $response = $this->requestHandler->handle($this->serverRequestFactory->create($event->getData()));
        $event->getServer()->send($event->getFd(), $response->getBody());
    }

    public function getSubscribedEvent(): string
    {
        return ReceiveEvent::class;
    }
}
