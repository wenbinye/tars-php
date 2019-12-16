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
    private $handler;
    /**
     * @var ResponseSenderInterface
     */
    private $responseSender;

    /**
     * @param RequestEvent $event
     */
    public function __invoke($event): void
    {
        $response = $this->handler->handle($this->serverRequestFactory->createServerRequest($event->getRequest()));
        $this->responseSender->send($response, $event->getResponse());
    }
}
