<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\protocol\TypeParser;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;

class RequestFactoryTest extends TestCase
{
    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function testEncode()
    {
        $reader = AnnotationReader::getInstance();
        $packer = new Packer(new TarsTypeFactory($reader));
        $parser = new TypeParser();
        $client = new HelloServiceClient();

        $factory = new RequestFactory(new MethodMetadataFactory($reader), $packer, new RequestIdGenerator());
        $payload['message'] = $packer->pack($parser->parse('string', ''), 'message', 'hello', 3);
        $request = $factory->createRequest($client, 'hello', $payload);
        $requestBody = $request->getBody();
        $unpackResult = \TUPAPI::decodeReqPacket($requestBody);
        $this->assertArrayHasKey('iVersion', $unpackResult);
        $this->assertArrayHasKey('iRequestId', $unpackResult);
        $this->assertArrayHasKey('sServantName', $unpackResult);
        $this->assertArrayHasKey('sFuncName', $unpackResult);
        $this->assertArrayHasKey('sBuffer', $unpackResult);
        // var_export($unpackResult);
        // iRequestId
        // iVersion
        // sServantName
        // sFuncName
        // sBuffer
    }
}

/**
 * @\wenbinye\tars\protocol\annotation\TarsClient("PHPTest.PHPTcpServer.obj")
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

class HelloServiceClient implements HelloServiceServant
{
    /**
     * {@inheritdoc}
     */
    public function hello($message)
    {
        // TODO: Implement hello() method.
    }
}
