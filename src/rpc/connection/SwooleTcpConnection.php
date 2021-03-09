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
        ServerSetting::OPEN_LENGTH_CHECK => true,
        ServerSetting::PACKAGE_LENGTH_TYPE => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,
        self::CONNECT_TIMEOUT => 5.0,
        self::RECV_TIMEOUT => 5.0,
        ServerSetting::PACKAGE_MAX_LENGTH => 10485760,
    ];

    public function setOptions(array $options): void
    {
        $this->settings = array_merge($this->settings, $options);
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
        if (!$client->connect($address->getHost(), $address->getPort(), $this->settings[self::CONNECT_TIMEOUT])) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }

        return $client;
    }

    protected function destroyResource(): void
    {
        /** @var Client|null $client */
        $client = $this->getResource();
        if (null !== $client && $client->isConnected()) {
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
        if (is_string($response) && '' !== $response) {
            return $response;
        }
        $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_RECEIVE_FAILED),
            isset($client->errCode) ? socket_strerror($client->errCode) : null);
    }
}
