<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\route\ServerAddressHolderInterface;

class RequestAttribute
{
    public const CLIENT_IP = '__IP';

    public const SERVER_ADDR = '__ADDR';

    public const TIME = '__TIME';

    public const TIMEOUT = '__TIMEOUT';

    public static function getRemoteAddress(RequestInterface $request): ?string
    {
        return $request->getAttribute(self::CLIENT_IP);
    }

    public static function setRemoteAddress(RequestInterface $request, string $remoteAddress): RequestInterface
    {
        return $request->withAttribute(self::CLIENT_IP, $remoteAddress);
    }

    public static function getServerAddress(RequestInterface $request): ?string
    {
        $attribute = $request->getAttribute(self::SERVER_ADDR);
        if ($attribute instanceof ServerAddressHolderInterface) {
            return $attribute->get()->getAddress();
        }

        if (isset($attribute)) {
            return (string) $attribute;
        }

        return null;
    }

    /**
     * @param RequestInterface                    $request
     * @param ServerAddressHolderInterface|string $address
     *
     * @return RequestInterface
     */
    public static function setServerAddress(RequestInterface $request, $address): RequestInterface
    {
        return $request->withAttribute(self::SERVER_ADDR, $address);
    }

    public static function getRequestTimeout(RequestInterface $request): ?float
    {
        return $request->getAttribute(self::TIMEOUT);
    }

    public static function setRequestTimeout(RequestInterface $request, float $timeout): RequestInterface
    {
        return $request->withAttribute(self::TIMEOUT, $timeout);
    }

    public static function getRequestTime(RequestInterface $request): int
    {
        return $request->getAttribute(self::TIME) ?? time();
    }
}
