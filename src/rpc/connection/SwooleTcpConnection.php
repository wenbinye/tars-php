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

    public const CONNECT_TIMEOUT = 'connect_timeout';
    public const RECV_TIMEOUT = 'recv_timeout';

    /**
     * @var array
     */
    protected $settings = [
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
        if (!$client->connect($address->getHost(), $address->getPort(), $this->settings[self::CONNECT_TIMEOUT] ?? 5.0)) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }

        return $client;
    }

    protected function destroyResource(): void
    {
        /** @var Client|null $client */
        $client = $this->getResource();
        if (null !== $client) {
            $client->close();
        }
    }

    protected function doSend(RequestInterface $request): string
    {
        /** @var Client $client */
        $client = $this->getResource();
        if (false === $client->send($request->getBody())) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }

        return $this->recv();
    }

    public function recv(): string
    {
        $client = $this->getResource();
        if (null === $client) {
            return '';
        }
        $response = $client->recv();
        $errCode = $client->errCode;
        if ('' === $response || false === $response) {
            $this->destroyResource();
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
                isset($errCode) ? socket_strerror($errCode) : null);
        }

        return $response;
    }
}
