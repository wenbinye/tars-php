<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use kuiper\helper\Arrays;
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
     * @var string|callable
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
     * @var string
     */
    private $dateFormat;

    /**
     * @var callable|null
     */
    private $requestFilter;

    /**
     * RequestLogMiddleware constructor.
     *
     * @param string|callable $template
     * @param array           $extra
     * @param int             $maxBodySize
     * @param string          $dateFormat
     * @param callable|null   $requestFilter
     */
    public function __construct(
        $template = self::MAIN,
        array $extra = ['params'],
        int $maxBodySize = 4096,
        string $dateFormat = '%d/%b/%Y:%H:%M:%S %z',
        ?callable $requestFilter = null
    ) {
        $this->format = $template;
        $this->extra = $extra;
        $this->maxBodySize = $maxBodySize;
        $this->dateFormat = $dateFormat;
        $this->requestFilter = $requestFilter;
    }

    protected function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $start = microtime(true);
        $response = null;
        try {
            $response = $next($request);

            return $response;
        } finally {
            if (null === $this->requestFilter
                || call_user_func($this->requestFilter, $request, $response)) {
                $responseTime = (microtime(true) - $start) * 1000;
                $this->format($this->prepareMessageContext($request, $response, $responseTime));
            }
        }
    }

    protected function prepareMessageContext(RequestInterface $request, ?ResponseInterface $response, float $responseTime): array
    {
        $time = round($responseTime, 2);

        $statusCode = isset($response) ? $response->getReturnCode() : ErrorCode::UNKNOWN;
        $responseBodySize = isset($response) ? strlen($response->getBody()) : 0;
        $message = [
            'remote_addr' => RequestAttribute::getRemoteAddress($request) ?? '-',
            'time_local' => strftime($this->dateFormat),
            'referer' => $this->getReferer($request),
            'request' => $this->formatRequest($request, $response),
            'request_id' => $request->getRequestId(),
            'servant' => $request->getServantName(),
            'method' => $request->getFuncName(),
            'status' => $statusCode,
            'body_bytes_sent' => $responseBodySize,
            'request_time' => $time,
        ];
        $extra = [];
        foreach ($this->extra as $name) {
            if ('params' === $name) {
                $param = str_replace('"', "'", (string) json_encode($this->getParameters($request)));
                $extra['params'] = strlen($param) > $this->maxBodySize
                    ? sprintf('%s...%d more', substr($param, 0, $this->maxBodySize), strlen($param) - $this->maxBodySize)
                    : $param;
            }
        }
        $message['extra'] = array_filter($extra);

        return $message;
    }

    protected function format(array $messageContext): void
    {
        if (is_string($this->format)) {
            $this->logger->info(strtr($this->format, Arrays::mapKeys($messageContext, function ($key): string {
                return '$'.$key;
            })), $messageContext['extra'] ?? []);
        } elseif (is_callable($this->format)) {
            $this->logger->info(call_user_func($this->format, $messageContext));
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

    private function formatRequest(RequestInterface $request, ?ResponseInterface $response): string
    {
        $req = null !== $response ? $response->getRequest() : $request;

        return sprintf('TCP %s/%s#%s %d',
            RequestAttribute::getServerAddress($req),
            $request->getServantName(),
            $request->getFuncName(),
            $request->getVersion());
    }

    protected function getReferer(RequestInterface $request): string
    {
        if ($request instanceof ClientRequestInterface) {
            $serverRequest = ServerRequestHolder::getRequest();

            return null !== $serverRequest ? $this->getReferer($serverRequest) : '';
        }

        $referer = $request->getContext()[AddRequestReferer::CONTEXT_KEY] ?? null;

        return isset($referer) ? (string) $referer : '';
    }
}
