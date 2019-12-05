<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class SocketClient implements ClientInterface
{
    public function send(RequestInterface $request)
    {
        $sock = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (false === $sock) {
            ErrorCode::fromValue(ErrorCode::TARS_SOCKET_CREATE_FAILED);
        }

        if (!\socket_connect($sock, $sIp, $iPort)) {
            \socket_close($sock);
            throw new \Exception();
        }

        $totalLen = 0;
        $responseBuf = null;
        while (true) {
            if (microtime(true) - $time > $timeout) {
                \socket_close($sock);
                throw new \Exception();
            }
            //读取最多32M的数据
            $data = \socket_read($sock, 65536, PHP_BINARY_READ);

            if (empty($data)) {
                // 已经断开连接
                return '';
            } else {
                //第一个包
                if (null === $responseBuf) {
                    $responseBuf = $data;
                    //在这里从第一个包中获取总包长
                    $list = unpack('Nlen', substr($data, 0, 4));
                    $totalLen = $list['len'];
                } else {
                    $responseBuf .= $data;
                }

                //check if all package is receved
                if (strlen($responseBuf) >= $totalLen) {
                    \socket_close($sock);
                    break;
                }
            }
        }
    }
}
