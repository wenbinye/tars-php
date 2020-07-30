<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\fixtures\HelloServiceClient;
use wenbinye\tars\rpc\fixtures\HelloServiceServant;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\Request;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ServerRequestFactory;

class RequestFactoryTest extends TestCase
{
    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function testEncode()
    {
        $reader = AnnotationReader::getInstance();
        $packer = new Packer($reader);
        $client = new HelloServiceClient();
        $methodMetadataFactory = new MethodMetadataFactory($reader);

        $factory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $payload = ['hello'];
        /** @var Request $request */
        $request = $factory->createRequest($client, 'hello', $payload);
        $request->getRequestPacketBuilder()->setStatus(['status' => 1])
            ->setContext(['context' => 1]);
        $requestBody = $request->getBody();

        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(HelloServiceServant::class)
            ->andReturn($client);

        $serverRequestFactory = new ServerRequestFactory($container, $packer, $methodMetadataFactory,
            ['PHPTest.PHPTcpServer.obj' => HelloServiceServant::class]);
        $serverRequest = $serverRequestFactory->create($requestBody);
        $this->assertEquals($request->getStatus(), $serverRequest->getStatus());
        $this->assertEquals($request->getContext(), $serverRequest->getContext());
        $this->assertEquals($request->getRequestId(), $serverRequest->getRequestId());
        $this->assertEquals('PHPTest.PHPTcpServer.obj', $serverRequest->getServantName());
        $this->assertEquals('hello', $serverRequest->getFuncName());
    }

//        $unpackResult = \TUPAPI::decodeReqPacket($requestBody);
//        $this->assertArrayHasKey('iVersion', $unpackResult);
//        $this->assertArrayHasKey('iRequestId', $unpackResult);
//        $this->assertArrayHasKey('sServantName', $unpackResult);
//        $this->assertArrayHasKey('sFuncName', $unpackResult);
//        $this->assertArrayHasKey('sBuffer', $unpackResult);
    // var_export($unpackResult);
    // iRequestId
    // iVersion
    // sServantName
    // sFuncName
    // sBuffer
}
