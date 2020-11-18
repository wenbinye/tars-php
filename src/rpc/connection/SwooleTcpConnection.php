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

    /**
     * @var array
     */
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
        return new Client(SWOOLE_SOCK_TCP);
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
        $client = $this->getResource();
        if (null !== $client) {
            $client->close();
        }
    }

    protected function afterSend(): void
    {
        $this->destroyResource();
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(RequestInterface $request): string
    {
        /** @var Client $client */
        $client = $this->getResource();
        $client->send($request->getBody());
        $response = $client->recv();
        $errCode = $client->errCode;
        if ('' === $response || false === $response) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
                isset($errCode) ? socket_strerror($errCode) : null);
        }

        return $response;
    }
}
