<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\ClientRequestInterface;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestHolder;

/**
 * Formats log messages using variable substitutions for requests, responses,
 * and other transactional data.
 *
 * The following variable substitutions are supported:
 *
 * - $remote_addr:    Client address
 * - $referer:        Client application name
 * - $time_local:     Time
 * - $request:        Servant name and function name
 * - $status:         0 success, other fail
 * - $body_bytes_sent: Response body bytes
 * - $request_time:    Request time
 * - $request_id:      Request id
 * - $servant:         Servant name
 * - $method:          Method name
 */
abstract class AbstractRequestLog implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const MAIN = '$remote_addr [$time_local] "$request" $status $body_bytes_sent "$referer" rt=$request_time';

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
    public function __construct(string $template = self::MAIN, array $extra = ['params'], int $maxBodySize = 4096)
    {
        $this->format = $template;
        $this->extra = $extra;
        $this->maxBodySize = $maxBodySize;
    }

    protected function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $start = microtime(true);
        try {
            $response = $next($request);
            $this->writeLog($request, $response, (microtime(true) - $start) * 1000);

            return $response;
        } catch (\Throwable $e) {
            $this->writeLog($request, null, (microtime(true) - $start) * 1000);
            throw $e;
        }
    }

    protected function writeLog(RequestInterface $request, ?ResponseInterface $response, float $responseTime): void
    {
        $time = sprintf('%.2f', $responseTime);

        $statusCode = isset($response) ? $response->getReturnCode() : ErrorCode::UNKNOWN;
        $responseBodySize = isset($response) ? strlen($response->getBody()) : 0;
        $message = strtr($this->format, [
            '$remote_addr' => RequestAttribute::getRemoteAddress($request) ?? '-',
            '$time_local' => strftime('%d/%b/%Y:%H:%M:%S %z'),
            '$referer' => $this->getReferer($request),
            '$request' => $this->formatRequest(isset($response) ? $response->getRequest() : $request),
            '$request_id' => $request->getRequestId(),
            '$servant' => $request->getServantName(),
            '$method' => $request->getFuncName(),
            '$status' => $statusCode,
            '$body_bytes_sent' => $responseBodySize,
            '$request_time' => $time,
        ]);
        $extra = [];
        foreach ($this->extra as $name) {
            if ('params' === $name) {
                $param = json_encode($this->getParameters($request));
                $extra['params'] = strlen($param) > $this->maxBodySize
                    ? sprintf('%s...%d more', substr($param, 0, $this->maxBodySize), strlen($param) - $this->maxBodySize)
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
        return sprintf('TCP %s/%s#%s %d',
            RequestAttribute::getServerAddress($request),
            $request->getServantName(),
            $request->getFuncName(),
            $request->getVersion());
    }

    /**
     * @return mixed|string
     */
    protected function getReferer(RequestInterface $request)
    {
        if ($request instanceof ClientRequestInterface) {
            $serverRequest = ServerRequestHolder::getRequest();

            return $serverRequest ? $this->getReferer($serverRequest) : '';
        }

        return $request->getContext()[AddRequestReferer::CONTEXT_KEY] ?? '';
    }
}
