<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use kuiper\swoole\coroutine\Coroutine;

class ServerRequestHolder
{
    private const REQUEST_CONTEXT_KEY = '__TarsServantRequest';

    public static function setRequest(ServerRequestInterface $request): void
    {
        Coroutine::getContext()->offsetSet(self::REQUEST_CONTEXT_KEY, $request);
    }

    public static function getRequest(): ?ServerRequestInterface
    {
        return Coroutine::getContext()->offsetGet(self::REQUEST_CONTEXT_KEY);
    }
}
