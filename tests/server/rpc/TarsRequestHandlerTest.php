<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use kuiper\annotations\AnnotationReader;
use Mockery\MockInterface;
use Monolog\Test\TestCase;
use Psr\Container\ContainerInterface;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\exception\RequestException;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ServerRequestFactory;
use wenbinye\tars\rpc\message\tup\RequestPacket;
use wenbinye\tars\rpc\ServantProxyGenerator;
use wenbinye\tars\rpc\ServantProxyGeneratorInterface;
use wenbinye\tars\rpc\server\DefaultErrorHandler;
use wenbinye\tars\rpc\server\TarsRequestHandler;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\fixtures\AnotherHelloServant;
use wenbinye\tars\server\fixtures\HelloServant;
use wenbinye\tars\server\fixtures\HelloService;

class TarsRequestHandlerTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;
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
        /** @var ContainerInterface|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $annotationReader = AnnotationReader::getInstance();
        $packer = new Packer($annotationReader);
        $methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $this->container = $container;
        $this->proxyGenerator = new ServantProxyGenerator($annotationReader);
        $this->tarsClient = \Mockery::mock(TarsClientInterface::class);
        $this->requestFactory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $this->serverRequestFactory = new ServerRequestFactory($container, $packer, $methodMetadataFactory);
        $this->responseFactory = new ResponseFactory($packer);
        $this->tarsRequestHandler = new TarsRequestHandler($packer, new DefaultErrorHandler(), null);
        $this->serverRequestFactory->register('PHPTest.PHPTcpServer.obj', HelloServant::class);
    }

    public function testName()
    {
        $this->container->shouldReceive('has')
            ->with(HelloServant::class)
            ->andReturn(true);
        $this->container->shouldReceive('get')
            ->with(HelloServant::class)
            ->andReturn(new HelloService());
        $message = 'world';
        $servantClass = $this->proxyGenerator->generate(HelloServant::class);
        $request = $this->requestFactory->createRequest(new $servantClass($this->tarsClient), 'hello', [$message]);

        $response = $this->tarsRequestHandler->handle($this->serverRequestFactory->create($request->getBody()));
        // var_export($response);
        $this->assertTrue($response->isSuccess());

        $clientResponse = $this->responseFactory->create($response->getBody(), $request);
        // var_export($clientResponse);
        $this->assertEquals('hello '.$message, $clientResponse->getReturnValues()[0]->getData());
    }

    public function testNullReturnValue()
    {
        $this->container->shouldReceive('has')
            ->with(HelloServant::class)
            ->andReturn(true);
        $this->container->shouldReceive('get')
            ->with(HelloServant::class)
            ->andReturn(new class() implements HelloServant {
                public function hello($message)
                {
                    return null;
                }
            });
        $message = 'world';
        $servantClass = $this->proxyGenerator->generate(HelloServant::class);
        $request = $this->requestFactory->createRequest(new $servantClass($this->tarsClient), 'hello', [$message]);

        $response = $this->tarsRequestHandler->handle($this->serverRequestFactory->create($request->getBody()));
        // var_export($response);
        $this->assertTrue($response->isSuccess());

        $clientResponse = $this->responseFactory->create($response->getBody(), $request);
        var_export($clientResponse);
        $this->assertEquals('hello '.$message, $clientResponse->getReturnValues()[0]->getData());
    }

    public function testNoServant1()
    {
        $this->container->shouldReceive('has')
            ->with(HelloServant::class)
            ->andReturn(false);
        $this->expectException(RequestException::class);
        $message = 'world';
        $servantClass = $this->proxyGenerator->generate(HelloServant::class);
        $request = $this->requestFactory->createRequest(new $servantClass($this->tarsClient), 'hello', [$message]);

        $this->tarsRequestHandler->handle($this->serverRequestFactory->create($request->getBody()));
    }

    public function testNoServant()
    {
        $message = 'world';
        $servantClass = $this->proxyGenerator->generate(AnotherHelloServant::class);
        $request = $this->requestFactory->createRequest(new $servantClass($this->tarsClient), 'hello', [$message]);
        $requestException = new RequestException(RequestPacket::builder()->build(),
            ErrorCode::SERVER_NO_SERVANT_ERR()->message, ErrorCode::SERVER_NO_SERVANT_ERR);

        $clientResponse = $this->responseFactory->create($requestException->toResponseBody(), $request);
        // var_export($clientResponse);
        $this->assertEquals(ErrorCode::SERVER_NO_SERVANT_ERR, $clientResponse->getReturnCode());
        // $this->assertEquals('hello '.$message, $clientResponse->getReturnValues()[0]->getData());
    }
}
