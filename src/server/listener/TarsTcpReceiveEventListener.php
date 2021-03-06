<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\swoole\event\ReceiveEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\RequestException;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\ServerRequestFactoryInterface;
use wenbinye\tars\rpc\message\ServerRequestHolder;
use wenbinye\tars\rpc\message\ServerRequestInterface;
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
     * @var string
     */
    private $healthCheckServant;

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
        $this->healthCheckServant = $serverProperties->getServerName().'.HealthCheckObj';
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        Assert::isInstanceOf($event, ReceiveEvent::class);
        // TODO: 会不会有数据包不完整情况？
        /** @var ReceiveEvent $event */
        $server = $event->getServer();

        try {
            $request = $this->serverRequestFactory->create($event->getData());
            $connectionInfo = $server->getConnectionInfo($event->getClientId());

            if (null === $connectionInfo) {
                $this->logger->error(static::TAG.'cannot get connection info');

                return;
            }
            if (!isset($this->servants[$connectionInfo->getServerPort()][$request->getServantName()])
                && !$this->isHealthCheckRequest($request)) {
                $this->logger->warning(static::TAG.'cannot find adapter match servant, check config file');
                throw new RequestException(RequestPacket::fromRequest($request), 'Unknown servant '.$request->getServantName(), ErrorCode::SERVER_NO_SERVANT_ERR);
            }
            /** @var ServerRequestInterface $request */
            $request = RequestAttribute::setRemoteAddress($request, $connectionInfo->getRemoteIp());

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

    private function isHealthCheckRequest(ServerRequestInterface $request): bool
    {
        return $request->getServantName() === $this->healthCheckServant;
    }
}
