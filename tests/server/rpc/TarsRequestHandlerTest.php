<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Test\TestCase;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\rpc\MethodMetadataFactory;
use wenbinye\tars\rpc\RequestFactory;
use wenbinye\tars\rpc\RequestIdGenerator;
use wenbinye\tars\rpc\RpcPacker;
use wenbinye\tars\server\Config;

class TarsRequestHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        Config::parseFile(__DIR__.'/../fixtures/PHPTest.PHPHttpServer.config.conf');
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function testName()
    {
        $annotationReader = new AnnotationReader();
        $requestFactory = new RequestFactory(new RequestIdGenerator());
        $methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $packer = new Packer(new TarsTypeFactory($annotationReader));
        $rpcPacker = new RpcPacker($packer);

        $fooServantImpl = new HelloService();
        $tarsRequestHandler = new TarsRequestHandler([$fooServantImpl], $annotationReader, $packer);

        $method = $methodMetadataFactory->create($fooServantImpl, 'hello');
        $parameters = ['world'];
        $request = $requestFactory->createRequest($method->getServantName(), $method->getMethodName(),
            $rpcPacker->packRequest($method, $parameters, $requestFactory->getVersion()));
        $serverRequest = new ServerRequest($request->getBody());

        $response = $tarsRequestHandler->handle($serverRequest);
        // var_export($response);
        $this->assertTrue($response->isSuccess());
    }
}

/**
 * @TarsServant(servant="PHPTest.PHPTcpServer.obj")
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
