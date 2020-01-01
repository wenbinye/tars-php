<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use wenbinye\tars\server\event\ReceiveEvent;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\ServerRequest;

class TarsRequestHandler implements EventListenerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    /**
     * TarsRequestHandler constructor.
     */
    public function __construct(RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param ReceiveEvent $event
     */
    public function __invoke($event): void
    {
        $response = $this->requestHandler->handle(new ServerRequest($event->getData()));
        $event->getSwooleServer()->send($event->getFd(), $response->getBody());
    }

    public function getSubscribedEvent(): string
    {
        return ReceiveEvent::class;
    }
}
