<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use Psr\Http\Server\RequestHandlerInterface;
use wenbinye\tars\server\event\RequestEvent;
use wenbinye\tars\server\http\ResponseSenderInterface;
use wenbinye\tars\server\http\ServerRequestFactoryInterface;

class RequestEventListener implements EventListenerInterface
{
    /**
     * @var ServerRequestFactoryInterface
     */
    private $serverRequestFactory;
    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;
    /**
     * @var ResponseSenderInterface
     */
    private $responseSender;

    /**
     * RequestEventListener constructor.
     */
    public function __construct(ServerRequestFactoryInterface $serverRequestFactory, RequestHandlerInterface $handler, ResponseSenderInterface $responseSender)
    {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->requestHandler = $handler;
        $this->responseSender = $responseSender;
    }

    /**
     * @param RequestEvent $event
     */
    public function __invoke($event): void
    {
        $response = $this->requestHandler->handle($this->serverRequestFactory->createServerRequest($event->getRequest()));
        $this->responseSender->send($response, $event->getResponse());
    }

    public function getSubscribedEvent(): string
    {
        return RequestEvent::class;
    }
}
