<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\server\event\RequestEvent;
use wenbinye\tars\server\http\ResponseSenderInterface;
use wenbinye\tars\server\http\ServerRequestFactoryInterface;

class RequestEventListener implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
        try {
            $this->logger->info('on request');
            $response = $this->requestHandler->handle($this->serverRequestFactory->createServerRequest($event->getRequest()));
            $this->responseSender->send($response, $event->getResponse());
        } catch (\Exception $e) {
            $this->logger->error('Uncaught exception: '.get_class($e).': '.$e->getMessage()."\n".$e->getTraceAsString());
        }
    }

    public function getSubscribedEvent(): string
    {
        return RequestEvent::class;
    }
}
