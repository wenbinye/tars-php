<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;
use function Zend\Diactoros\normalizeUploadedFiles;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;

class ZendDiactorosServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest(Request $swooleRequest): ServerRequestInterface
    {
        $server = array_change_key_case($swooleRequest->server, CASE_UPPER);
        $headers = $swooleRequest->header;
        foreach ($headers as $key => $val) {
            $server['HTTP_'.str_replace('-', '_', strtoupper($key))] = $val;
        }
        $server['HTTP_COOKIE'] = isset($request->cookie) ? $this->cookieString($swooleRequest->cookie) : '';
        $serverRequest = ServerRequestFactory::fromGlobals(
            $server,
            $swooleRequest->get,
            $swooleRequest->post,
            $swooleRequest->cookie,
            $swooleRequest->files ? normalizeUploadedFiles($swooleRequest->files) : null
        );
        $body = $swooleRequest->rawContent();
        if (false !== $body) {
            $serverRequest = $serverRequest->withBody(new Stream($body));
        }

        return $serverRequest;
    }

    /**
     * Converts array to cookie string.
     */
    private function cookieString(array $cookie): string
    {
        return implode('; ', array_map(static function ($key, $value) {
            return $key.'='.$value;
        }, array_keys($cookie), array_values($cookie)));
    }
}
