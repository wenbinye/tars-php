<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use kuiper\annotations\AnnotationReader;
use Mockery\MockInterface;
use Monolog\Test\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ServerRequestFactory;
use wenbinye\tars\rpc\ServantProxyGenerator;
use wenbinye\tars\rpc\ServantProxyGeneratorInterface;
use wenbinye\tars\rpc\server\DefaultErrorHandler;
use wenbinye\tars\rpc\server\TarsRequestHandler;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\fixtures\HelloServant;

class TarsRequestFatalErrorTest extends TestCase
{
    /**
     * @var ClientRequestFactory
     */
    private $requestFactory;
    /**
     * @var ServerRequestFactory
     */
    private $serverRequestFactory;
    /**
     * @var ResponseFactory
     */
    private $responseFactory;
    /**
     * @var TarsRequestHandler
     */
    private $tarsRequestHandler;
    /**
     * @var TarsClientInterface
     */
    private $tarsClient;
    /**
     * @var ServantProxyGeneratorInterface
     */
    private $proxyGenerator;

    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        $servant = \Mockery::mock(HelloServant::class);
        $servant->shouldReceive('hello')
            ->andReturnUsing(function ($message) {
                $call = function (int $value) {
                };
                $call($message);
            });
        /** @var ContainerInterface|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->withArgs([HelloServant::class])
            ->andReturn(true);
        $container->shouldReceive('get')
            ->withArgs([HelloServant::class])
            ->andReturn($servant);

        $annotationReader = AnnotationReader::getInstance();
        $packer = new Packer($annotationReader);
        $methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $this->proxyGenerator = new ServantProxyGenerator($annotationReader);
        $this->tarsClient = \Mockery::mock(TarsClientInterface::class);
        $this->requestFactory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $this->serverRequestFactory = new ServerRequestFactory($container, $packer, $methodMetadataFactory);
        $this->responseFactory = new ResponseFactory($packer);
        $errorHandler = new DefaultErrorHandler();
        $errorHandler->setLogger(new NullLogger());
        $this->tarsRequestHandler = new TarsRequestHandler($packer, $errorHandler, null);
        $this->serverRequestFactory->register('PHPTest.PHPTcpServer.obj', HelloServant::class);
    }

    public function testName()
    {
        $message = 'world';
        $servantClass = $this->proxyGenerator->generate(HelloServant::class);
        $request = $this->requestFactory->createRequest(new $servantClass($this->tarsClient), 'hello', [$message]);

        $response = $this->tarsRequestHandler->handle($this->serverRequestFactory->create($request->getBody()));
        // var_export($response);
        $this->assertFalse($response->isSuccess());

        $clientResponse = $this->responseFactory->create($response->getBody(), $request);
        //  var_export($clientResponse);
        $this->assertEquals(ErrorCode::UNKNOWN, $clientResponse->getReturnCode());
        $this->assertStringContainsString('must be of the type integer', $clientResponse->getMessage());
    }
}
