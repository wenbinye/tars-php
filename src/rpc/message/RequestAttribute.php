<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class RequestAttribute
{
    public const CLIENT_IP = '__IP';

    public const SERVER_ADDR = '__ADDR';

    public const TIME = '__TIME';

    public static function getRemoteAddress(RequestInterface $request): ?string
    {
        return $request->getAttribute(self::CLIENT_IP);
    }

    public static function getServerAddress(RequestInterface $request): ?string
    {
        return $request->getAttribute(self::SERVER_ADDR);
    }

    public static function getRequestTime(RequestInterface $request): int
    {
        return $request->getAttribute(self::TIME) ?? time();
    }
}
