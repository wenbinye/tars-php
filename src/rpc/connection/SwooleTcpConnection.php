<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\SwooleSetting;
use Swoole\Client;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\RequestInterface;

class SwooleTcpConnection extends AbstractConnection
{
    private $settings = [
        SwooleSetting::OPEN_LENGTH_CHECK => 1,
        SwooleSetting::PACKAGE_LENGTH_TYPE => 'N',
        SwooleSetting::PACKAGE_MAX_LENGTH => 2000000,
    ];

    public function setOptions(array $options): void
    {
        $this->settings = $options;
    }

    /**
     * @return Client
     */
    protected function createSwooleClient()
    {
        return new Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
    }

    /**
     * {@inheritdoc}
     */
    protected function createResource()
    {
        $client = $this->createSwooleClient();
        $client->set($this->settings);
        $route = $this->getRoute();
        if (!$client->connect($route->getHost(), $route->getPort(), $route->getTimeout() / 1000)) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }
        $this->logger && $this->logger->debug('[SwooleTcpConnection] connected', ['route' => (string) $route]);

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function destroyResource(): void
    {
        /** @var Client $client */
        $client = $this->getResource();
        $client->close();
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(RequestInterface $request): string
    {
        /** @var Client $client */
        $client = $this->getResource();
        if (!$client->send($request->getBody())) {
            $this->disconnect();
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_SEND_FAILED));
        }
        //读取最多32M的数据
        $response = $client->recv();

        if ('' === $response) {
            $this->disconnect();
            // 已经断开连接
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED));
        } elseif (false === $response) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED), socket_strerror($client->errCode));
        }

        return $response;
    }
}
