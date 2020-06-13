<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class RequestAttribute
{
    public const CLIENT_IP = '__ip__';

    public const SERVER_ADDR = '__addr__';

    public static function getRemoteAddress(RequestInterface $request): ?string
    {
        return $request->getAttribute(self::CLIENT_IP);
    }

    public static function getServerAddress(RequestInterface $request): ?string
    {
        return $request->getAttribute(self::SERVER_ADDR);
    }
}
