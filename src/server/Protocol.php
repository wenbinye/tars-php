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
    public const HTTP = 'http';
    public const HTTP2 = 'http2';
    public const WEBSOCKET = 'websocket';
    public const GRPC = 'grpc';
    public const JSONRPC = 'jsonrpc';
    public const TARS = 'tars';

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
