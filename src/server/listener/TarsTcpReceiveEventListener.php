<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\swoole\event\ReceiveEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\RequestException;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\ServerRequestFactoryInterface;
use wenbinye\tars\rpc\message\ServerRequestHolder;
use wenbinye\tars\rpc\message\tup\RequestPacket;
use wenbinye\tars\rpc\server\RequestHandlerInterface;
use wenbinye\tars\server\ServerProperties;

class TarsTcpReceiveEventListener implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var array
     */
    private $servants;
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
    public function __construct(ServerProperties $serverProperties, ServerRequestFactoryInterface $serverRequestFactory, RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->serverRequestFactory = $serverRequestFactory;
        foreach ($serverProperties->getAdapters() as $adapter) {
            $this->servants[$adapter->getEndpoint()->getPort()][$adapter->getServantName()] = true;
        }
    }

    /**
     * @param ReceiveEvent $event
     */
    public function __invoke($event): void
    {
        // TODO: 会不会有数据包不完整情况？
        $server = $event->getServer();

        try {
            $request = $this->serverRequestFactory->create($event->getData());
            $connectionInfo = $server->getConnectionInfo($event->getClientId());

            if (!$connectionInfo) {
                $this->logger->error(static::TAG.'cannot get connection info');

                return;
            }
            if (!isset($this->servants[$connectionInfo->getServerPort()][$request->getServantName()])) {
                $this->logger->warning(static::TAG.'cannot find adapter match servant, check config file');
                throw new RequestException(RequestPacket::fromRequest($request), 'Unknown servant '.$request->getServantName(), ErrorCode::SERVER_NO_SERVANT_ERR);
            }
            $request = $request->withAttribute(RequestAttribute::CLIENT_IP, $connectionInfo->getRemoteIp());

            ServerRequestHolder::setRequest($request);
            $response = $this->requestHandler->handle($request);
            $server->send($event->getClientId(), $response->getBody());
        } catch (RequestException $e) {
            $server->send($event->getClientId(), $e->toResponseBody());
        }
    }

    public function getSubscribedEvent(): string
    {
        return ReceiveEvent::class;
    }
}
