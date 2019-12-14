<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Swoole\Http\Server;
use wenbinye\tars\support\Enum;

/**
 * Class SwooleServerType.
 *
 * @property string $server
 * @property array  $settings
 * @property array  $events
 */
class SwooleServerType extends Enum
{
    const HTTP = 'http';
    const HTTP2 = 'http2';
    const WEBSOCKET = 'websocket';
    const TCP = 'tcp';
    const UDP = 'udp';

    protected static $PROPERTIES = [
        'server' => [
            self::HTTP => Server::class,
            self::HTTP2 => Server::class,
            self::WEBSOCKET => Server::class,
            self::TCP => \Swoole\Server::class,
            self::UDP => \Swoole\Server::class,
        ],
        'settings' => [
            self::HTTP => [
                SwooleServerSetting::OPEN_HTTP_PROTOCOL => true,
                SwooleServerSetting::OPEN_HTTP2_PROTOCOL => false,
            ],
            self::HTTP2 => [
                SwooleServerSetting::OPEN_HTTP_PROTOCOL => false,
                SwooleServerSetting::OPEN_HTTP2_PROTOCOL => true,
            ],
            self::WEBSOCKET => [
                SwooleServerSetting::OPEN_WEBSOCKET_PROTOCOL => true,
                SwooleServerSetting::OPEN_HTTP2_PROTOCOL => false,
            ],
            self::TCP => [
                SwooleServerSetting::OPEN_HTTP_PROTOCOL => false,
                SwooleServerSetting::OPEN_HTTP2_PROTOCOL => false,
            ],
            self::UDP => [
                SwooleServerSetting::OPEN_HTTP_PROTOCOL => false,
                SwooleServerSetting::OPEN_HTTP2_PROTOCOL => false,
            ],
        ],
        'events' => [
            self::HTTP => [SwooleEvent::REQUEST],
            self::HTTP2 => [SwooleEvent::REQUEST],
            self::WEBSOCKET => [SwooleEvent::REQUEST, SwooleEvent::MESSAGE],
            self::TCP => [SwooleEvent::RECEIVE],
            self::UDP => [SwooleEvent::RECEIVE],
        ],
    ];
}
