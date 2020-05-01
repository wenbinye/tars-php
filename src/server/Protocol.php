<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\helper\Enum;
use kuiper\swoole\constants\ServerType;

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
            self::HTTP => ServerType::HTTP,
            self::HTTP2 => ServerType::HTTP2,
            self::WEBSOCKET => ServerType::WEBSOCKET,
            self::GRPC => ServerType::HTTP2,
        ],
    ];

    public function isHttpProtocol(): bool
    {
        return $this->serverType && ServerType::fromValue($this->serverType)->isHttpProtocol();
    }
}
