<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use wenbinye\tars\support\Enum;

/**
 * Class Protocol.
 *
 * @property string $serverType
 */
class Protocol extends Enum
{
    const HTTP = 'http';
    const HTTP2 = 'http2';
    const WEBSOCKET = 'websocket';
    const GRPC = 'grpc';
    const JSONRPC = 'jsonrpc';
    const TARS = 'tars';

    protected static $PROPERTIES = [
        'serverType' => [
            self::HTTP => SwooleServerType::HTTP,
            self::HTTP2 => SwooleServerType::HTTP2,
            self::WEBSOCKET => SwooleServerType::WEBSOCKET,
            self::GRPC => SwooleServerType::HTTP2,
        ],
    ];
}
