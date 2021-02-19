<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception;
use wenbinye\tars\rpc\message\RequestInterface;

class SocketTcpConnection extends AbstractConnection
{
    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var array
     */
    private $settings;

    /**
     * {@inheritdoc}
     *
     * @throws exception\CommunicationException
     */
    protected function createResource()
    {
        $socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (false === $socket) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CREATE_FAILED));
        }
        $address = $this->getAddress();
        if (!\socket_connect($socket, $address->getHost(), $address->getPort())) {
            \socket_close($socket);
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CONNECT_FAILED));
        }

        return $socket;
    }

    /**
     * {@inheritdoc}
     */
    protected function destroyResource(): void
    {
        \socket_close($this->getResource());
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(RequestInterface $request): string
    {
        $socket = $this->getResource();
        $requestData = $request->getBody();
        if (!\socket_write($socket, $requestData, strlen($requestData))) {
            $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_SEND_FAILED));
        }

        return $this->recv();
    }

    public function recv(): string
    {
        $socket = $this->getResource();
        $time = microtime(true);
        $timeout = ($this->settings[SwooleTcpConnection::RECV_TIMEOUT] ?? 5.0) * 10000;
        $responseLength = 0;
        $response = null;
        while (true) {
            if (1000 * (microtime(true) - $time) > $timeout) {
                $this->onConnectionError(ErrorCode::fromValue(ErrorCode::TARS_SOCKET_SELECT_TIMEOUT));
            }
            //读取最多32M的数据
            $data = \socket_read($socket, 65536, PHP_BINARY_READ);

            if (empty($data)) {
                // 已经断开连接
                return '';
            }

            //第一个包
            if (null === $response) {
                $response = $data;
                //在这里从第一个包中获取总包长
                $list = unpack('Nlen', substr($data, 0, 4));
                $responseLength = $list['len'];
            } else {
                $response .= $data;
            }

            //check if all package is receved
            if (strlen($response) >= $responseLength) {
                break;
            }
        }

        return $response;
    }

    public function setOptions(array $options): void
    {
        $this->settings = $options;
    }
}
