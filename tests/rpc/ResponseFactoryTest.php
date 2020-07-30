<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\exception\RequestException;
use wenbinye\tars\rpc\fixtures\HelloServiceClient;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\Request;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ServerResponse;

class ResponseFactoryTest extends TestCase
{
    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function testReturnValue()
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
        $request->getRequestPacketBuilder()->setStatus(['a' => 1])
            ->setContext(['b' => 1]);
        $returnValues = $tarsRpcPacker->packResponse($request->getMethod(), ['hello, world'], $request->getVersion());
        $serverResponse = new ServerResponse($request, $returnValues, ErrorCode::SERVER_SUCCESS);

        $responseFactory = new ResponseFactory($packer);
        $response = $responseFactory->create($serverResponse->getBody(), $request);
        // print_r($response);
        $this->assertEquals('hello, world', $response->getReturnValues()[0]->getData());
    }

    public function testReturnCode()
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
        $request->getRequestPacketBuilder()
            ->setVersion(3)
            ->setStatus(['a' => 1])
            ->setContext(['b' => 1]);
        $requestException = new RequestException($request->getRequestPacketBuilder()->build(),
            'invalid request', 10);

        $responseFactory = new ResponseFactory($packer);
        $response = $responseFactory->create($requestException->toResponseBody(), $request);
        $this->assertEquals($requestException->getCode(), $response->getReturnCode());
        $this->assertEquals($requestException->getMessage(), $response->getMessage());
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
