<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;

/**
 * Formats log messages using variable substitutions for requests, responses,
 * and other transactional data.
 *
 * The following variable substitutions are supported:
 *
 * - $remote_addr:    Client address
 * - $time_local:     Time
 * - $request:        Servant name and function name
 * - $status:         0 success, other fail
 * - $body_bytes_sent: Response body bytes
 * - $request_time:    Request time
 * - $request_id:      Request id
 * - $servant:         Servant name
 * - $method:          Method name
 */
class RequestLog implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const MAIN = '$remote_addr [$time_local] "$request" $status $body_bytes_sent rt=$request_time';

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $extra;

    /**
     * @var int
     */
    private $maxBodySize;

    /**
     * RequestLogMiddleware constructor.
     */
    public function __construct(string $template = self::MAIN, array $extra = [], int $maxBodySize = 4096)
    {
        $this->format = $template;
        $this->extra = $extra;
        $this->maxBodySize = $maxBodySize;
    }

    public function process(RequestInterface $request, callable $next): ResponseInterface
    {
        $start = microtime(true);
        $response = null;
        try {
            $response = $next($request);

            return $response;
        } finally {
            $this->writeLog($request, $response, (microtime(true) - $start) * 1000);
        }
    }

    public function writeLog(RequestInterface $request, ?ResponseInterface $response, float $responseTime): void
    {
        $time = sprintf('%.2f', $responseTime);

        $statusCode = isset($response) ? $response->getReturnCode() : ErrorCode::UNKNOWN;
        $responseBodySize = isset($response) ? strlen($response->getBody()) : 0;
        $message = strtr($this->format, [
            '$remote_addr' => RequestAttribute::getRemoteAddress($request) ?? '-',
            '$time_local' => strftime('%d/%b/%Y:%H:%M:%S %z'),
            '$request' => $this->formatRequest($request),
            '$request_id' => $request->getRequestId(),
            '$servant' => $request->getServantName(),
            '$method' => $request->getFuncName(),
            '$status' => $statusCode,
            '$body_bytes_sent' => $responseBodySize,
            '$http_x_forwarded_for' => isset($ipList) ? implode(',', $ipList) : '',
            '$request_time' => $time,
        ]);
        $extra = [];
        foreach ($this->extra as $name) {
            if ('params' === $name) {
                $param = json_encode($this->getParameters($request));
                $extra['params'] = strlen($param) > $this->maxBodySize
                    ? sprintf('%s...%d more bytes', substr($param, 0, $this->maxBodySize), strlen($param) - $this->maxBodySize)
                    : $param;
            }
        }
        $extra = array_filter($extra);
        if (ErrorCode::SERVER_SUCCESS === $statusCode) {
            $this->logger->info($message, $extra);
        } else {
            $this->logger->error($message, $extra);
        }
    }

    private function getParameters(RequestInterface $request): array
    {
        $params = [];
        foreach ($request->getParameters() as $parameter) {
            if (!$parameter->isOut()) {
                $params[$parameter->getName()] = $parameter->getData();
            }
        }

        return $params;
    }

    private function formatRequest(RequestInterface $request)
    {
        return sprintf('%s/%s.%s',
            RequestAttribute::getServerAddress($request),
            $request->getServantName(),
            $request->getFuncName());
    }
}
