<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\swoole\constants\ServerSetting;
use Swoole\Client;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\RequestInterface;

class SwooleTcpConnection extends AbstractConnection
{
    protected const TAG = '['.__CLASS__.'] ';

    private $settings = [
        ServerSetting::OPEN_LENGTH_CHECK => 1,
        ServerSetting::PACKAGE_LENGTH_TYPE => 'N',
        ServerSetting::PACKAGE_MAX_LENGTH => 2000000,
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
        $address = $this->getAddress();
        if (!$client->connect($address->getHost(), $address->getPort(), $address->getTimeout() / 1000)) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }

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
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_SEND_FAILED));
        }
        //读取最多32M的数据
        $response = $client->recv();

        if ('' === $response) {
            // 已经断开连接
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CLOSED));
        } elseif (false === $response) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
                socket_strerror($client->errCode));
        }

        return $response;
    }
}
