<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use Monolog\Test\TestCase;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\Response;
use wenbinye\tars\server\rpc\ServerRequest;
use wenbinye\tars\server\rpc\ServerResponse;

class RpcPackerTest extends TestCase
{
    public function testRequestPackAndUnpack()
    {
        $servant = new HelloService();
        $requestFactory = new RequestFactory(, new RequestIdGenerator());
        $annotationReader = AnnotationReader::getInstance();
        $methodMetadataFactory = new MethodMetadataFactory($annotationReader);
        $packer = new Packer(new TarsTypeFactory($annotationReader));
        $rpcPacker = new TarsRpcPacker($packer);
        $method = $methodMetadataFactory->create($servant, 'hello');
        $parameters = ['world'];
        $request = $requestFactory->createRequest($method->getServantName(), $method->getMethodName(),
            $rpcPacker->packRequest($method, $parameters, $requestFactory->getVersion()));

        $unpackResult = \TUPAPI::decodeReqPacket($request->getBody());

        $unpackRequest = $rpcPacker->unpackRequest($method, $unpackResult['sBuffer'], $request->getVersion());
        $this->assertEquals($unpackRequest, $parameters);

        $serverRequest = new ServerRequest($request->getBody());
        $parameters[] = 'hello world';
        $packResponse = $rpcPacker->packResponse($method, $parameters, $serverRequest->getVersion());
        $serverResponse = new ServerResponse($serverRequest, $packResponse, ErrorCode::SERVER_SUCCESS, 'ok');
        $response = new Response($serverResponse->getBody(), $request);
        $unpackResponse = $rpcPacker->unpackResponse($method, $response->getPayload(), $response->getVersion());
        $this->assertEquals($unpackResponse, [end($parameters)]);
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
    }
}
