<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\server\ServerProperties;

/**
 * 在 context 中添加调用方信息.
 */
class AddRequestReferer implements ClientMiddlewareInterface
{
    public const CONTEXT_KEY = 'referer';
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    public function __invoke(ClientRequestInterface $request, callable $next): ResponseInterface
    {
        return $next($request->withContext(array_merge($request->getContext(), [
            self::CONTEXT_KEY => $this->serverProperties->getServerName(),
        ])));
    }
}
