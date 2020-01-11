<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use kuiper\annotations\AnnotationReader;
use Monolog\Test\TestCase;
use Psr\Container\ContainerInterface;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\server\Config;

class TarsRequestHandlerTest extends TestCase
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;
    /**
     * @var HelloService
     */
    private $servant;
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

    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        $this->servant = new HelloService();
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->andReturn($this->servant);
        TarsServant::register('PHPTest.PHPTcpServer.obj', HelloServiceServant::class);

        $annotationReader = AnnotationReader::getInstance();
        $packer = new Packer(new TarsTypeFactory($annotationReader));
        $methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $this->requestFactory = new RequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $this->serverRequestFactory = new ServerRequestFactory($container, $packer, $methodMetadataFactory);
        $this->responseFactory = new ResponseFactory($packer);
        $this->tarsRequestHandler = new TarsRequestHandler($packer);
    }

    public function testName()
    {
        $message = 'world';
        $request = $this->requestFactory->createRequest($this->servant, 'hello', [$message]);

        $response = $this->tarsRequestHandler->handle($this->serverRequestFactory->create($request->getBody()));
        // var_export($response);
        $this->assertTrue($response->isSuccess());

        $clientResponse = $this->responseFactory->create($response->getBody(), $request);
        // var_export($clientResponse);
        $this->assertEquals('hello '.$message, $clientResponse->getReturnValues()[0]->getData());
    }
}

/**
 * @TarsServant("PHPTest.PHPTcpServer.obj")
 */
interface HelloServiceServant
{
    /**
     * @TarsParameter(name = "message", type = "string")
     * @TarsReturnType(type = "string")
     *
     * @param string $message
     *
     * @return string
     */
    public function hello($message);
}

class HelloService implements HelloServiceServant
{
    /**
     * {@inheritdoc}
     */
    public function hello($message)
    {
        return 'hello '.$message;
    }
}
