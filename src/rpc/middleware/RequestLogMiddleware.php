<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\rpc\message\MessageInterface;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\MiddlewareInterface;

/**
 * Formats log messages using variable substitutions for requests, responses,
 * and other transactional data.
 *
 * The following variable substitutions are supported:
 *
 * - {ts}:             ISO 8601 date in GMT
 * - {date_iso_8601}   ISO 8601 date in GMT
 * - {date_common_log} Apache common log date using the configured timezone.
 * - {host}:           Server Host
 * - {port}:           Server Port
 * - {servant}:        Servant name
 * - {id}:             Request id
 * - {func}:           Function name
 * - {code}:           Status code of the response (if available)
 * - {message}:        message of the response  (if available)
 * - {params}:         Request parameters
 * - {return}:         Return values
 * - {request}:        Full Request
 * - {response}:       Full Response
 * - {req_body}:       Request body
 * - {res_body}:       Response body
 * - {attr_*}:         Request attribute
 */
class RequestLogMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const DEBUG = ">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{error}";
    public const SHORT = '[{ts}] "{servant}::{func}" {code}';

    private $template;

    /**
     * RequestLogMiddleware constructor.
     */
    public function __construct(string $template = self::DEBUG)
    {
        $this->template = $template;
    }

    public function __invoke(RequestInterface $request, callable $next): ResponseInterface
    {
        try {
            $response = $next($request);
            $this->logger->debug($this->format($request, $response));

            return $response;
        } catch (\Exception $e) {
            $this->logger->error($this->format($request, null, $e));
            throw $e;
        }
    }

    /**
     * Returns a formatted message string.
     *
     * @param RequestInterface  $request  Request that was sent
     * @param ResponseInterface $response Response that was received
     * @param \Exception        $error    Exception that was received
     *
     * @return string
     */
    public function format(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $error = null
    ) {
        $cache = [];
        $route = $response ? $response->getRequest()->getAttribute('route') : null;

        return preg_replace_callback(
            '/{\s*([A-Za-z_\-\.0-9]+)\s*}/',
            function (array $matches) use ($request, $response, $route, $error, &$cache) {
                if (isset($cache[$matches[1]])) {
                    return $cache[$matches[1]];
                }

                $result = '';
                switch ($matches[1]) {
                    case 'request':
                        $result = $this->stringfy($request);
                        break;
                    case 'response':
                        $result = $response ? $this->stringfy($response) : '';
                        break;
                    case 'req_body':
                        $result = $request->getBody();
                        break;
                    case 'res_body':
                        $result = $response ? $response->getBody() : 'NULL';
                        break;
                    case 'params':
                        $result = json_encode($this->getParameters($request));
                        break;
                    case 'return':
                        $result = $response ? json_encode($this->getReturnValues($response)) : 'NULL';
                        break;
                    case 'ts':
                    case 'date_iso_8601':
                        $result = gmdate('c');
                        break;
                    case 'date_common_log':
                        $result = date('d/M/Y:H:i:s O');
                        break;
                    case 'func':
                        $result = $request->getFuncName();
                        break;
                    case 'servant':
                        $result = $request->getServantName();
                        break;
                    case 'host':
                        $result = $route ? $route->getHost() : '';
                        break;
                    case 'port':
                        $result = $route ? $route->getPort() : '';
                        break;
                    case 'code':
                        $result = $response ? $response->getReturnCode() : 'NULL';
                        break;
                    case 'message':
                        $result = $response ? $response->getMessage() : 'NULL';
                        break;
                    case 'error':
                        $result = $error ? $error->getMessage() : 'NULL';
                        break;
                    default:
                        // handle prefixed dynamic headers
                        if (0 === strpos($matches[1], 'attr_')) {
                            $result = $request->getAttribute(substr($matches[1], 5));
                        }
                }

                $cache[$matches[1]] = $result;

                return $result;
            },
            $this->template
        );
    }

    private function stringfy(MessageInterface $message)
    {
        if ($message instanceof RequestInterface) {
            return sprintf('[%d]%s::%s(%s)', $message->getRequestId(), $message->getServantName(), $message->getFuncName(),
                json_encode($this->getParameters($message)));
        }

        if ($message instanceof ResponseInterface) {
            $route = $message->getRequest()->getAttribute('route');
            if ($route) {
                return sprintf('%s:%d->[%d %s](%s)', $route->getHost(), $route->getPort(), $message->getReturnCode(), $message->getMessage(),
                    json_encode($this->getReturnValues($message)));
            } else {
                return sprintf('[%d %s](%s)', $message->getReturnCode(), $message->getMessage(), json_encode($message->getReturnValues()));
            }
        }
    }

    private function getParameters(RequestInterface $request)
    {
        $params = [];
        foreach ($request->getParameters() as $parameter) {
            if (!$parameter->isOut()) {
                $params[$parameter->getName()] = $parameter->getData();
            }
        }

        return $params;
    }

    private function getReturnValues(ResponseInterface $message)
    {
        $return = [];
        foreach ($message->getReturnValues() as $returnValue) {
            $return[$returnValue->getName()] = $returnValue->getData();
        }

        return $return;
    }
}
