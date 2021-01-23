<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\middleware;

use kuiper\annotations\AnnotationReader;
use kuiper\helper\Arrays;
use kuiper\helper\Text;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\fixtures\HelloServiceClient;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\Request;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ServerResponse;
use wenbinye\tars\rpc\TarsRpcPacker;

class RequestLogTest extends TestCase
{
    public function testName()
    {
        $requestLog = new RequestLog();
        $logger = new TestLogger();
        $requestLog->setLogger($logger);
        $this->prepareRequest($requestLog);
        // var_export($logger->records);
        $this->assertCount(1, $logger->records);
    }

    public function testFilter()
    {
        $requestLog = new RequestLog(
            RequestLog::MAIN,
            [], 0, '%d/%b/%Y:%H:%M:%S %z',
            function (RequestInterface $request) {
                return !Text::startsWith($request->getServantName(), 'PHPTest.');
            });
        $logger = new TestLogger();
        $requestLog->setLogger($logger);
        $this->prepareRequest($requestLog);
        // var_export($logger->records);
        $this->assertCount(0, $logger->records);
    }

    public function testFormat()
    {
        $requestLog = new RequestLog(
            function (array $message): string {
                return json_encode(Arrays::select($message, [
                    'servant', 'method', 'status', 'remote_addr', 'time_local',
                    'referer', 'body_bytes_sent', 'request_id', 'request_time', 'extra',
                ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            });
        $logger = new TestLogger();
        $requestLog->setLogger($logger);
        $this->prepareRequest($requestLog);
        // var_export($logger->records);
        $this->assertCount(0, $logger->records);
    }

    /**
     * @param RequestLog $requestLog
     */
    protected function prepareRequest(RequestLog $requestLog): void
    {
        $reader = AnnotationReader::getInstance();
        $packer = new Packer($reader);
        $tarsRpcPacker = new TarsRpcPacker($packer);
        $client = new HelloServiceClient();
        $methodMetadataFactory = new MethodMetadataFactory($reader);

        $factory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $payload = ['hello'];
        /** @var Request $request */
        $request = $factory->createRequest($client, 'hello', $payload);
        $requestLog($request, function () use ($request, $tarsRpcPacker, $packer) {
            $returnValues = $tarsRpcPacker->packResponse($request->getMethod(), ['hello, world'], $request->getVersion());
            $serverResponse = new ServerResponse($request, $returnValues);

            $responseFactory = new ResponseFactory($packer);

            return $responseFactory->create($serverResponse->getBody(), $request);
        });
    }
}
