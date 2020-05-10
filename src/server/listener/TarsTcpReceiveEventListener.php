<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\swoole\event\ReceiveEvent;
use wenbinye\tars\rpc\route\ServerAddress;
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
        $server = $event->getServer();
        $request = $this->serverRequestFactory->create($event->getData());
        $connectionInfo = $server->getConnectionInfo($event->getClientId());
        if ($connectionInfo) {
            $request = $request->withAttribute('address',
                ServerAddress::create($connectionInfo->getRemoteIp(), $connectionInfo->getRemotePort()));
        }
        $response = $this->requestHandler->handle($request);
        $server->send($event->getClientId(), $response->getBody());
    }

    public function getSubscribedEvent(): string
    {
        return ReceiveEvent::class;
    }
}
