<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\fixtures\test\Address;
use wenbinye\tars\rpc\fixtures\test\User;
use wenbinye\tars\rpc\fixtures\test\UserClient;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\Request;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ResponseFactory;
use wenbinye\tars\rpc\message\ServerResponse;

class ResponseMaxPacketTest extends TestCase
{
    public function testReturnCode()
    {
        $reader = AnnotationReader::getInstance();
        $packer = new Packer($reader);
        $tarsRpcPacker = new TarsRpcPacker($packer);
        $client = new UserClient();
        $methodMetadataFactory = new MethodMetadataFactory($reader);

        $factory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $payload = [];
        /** @var Request $request */
        $request = $factory->createRequest($client, 'findAll', $payload);
        $request->getRequestPacketBuilder()
            ->setVersion(3)
            ->setStatus(['a' => 1])
            ->setContext(['b' => 1]);

        $returnValues = $tarsRpcPacker->packResponse($request->getMethod(), $this->createUsers(), $request->getVersion());
        $serverResponse = new ServerResponse($request, $returnValues);

        $responseFactory = new ResponseFactory($packer);
        error_log('response size: '.strlen($serverResponse->getBody()));
        file_put_contents('/tmp/b.data', $serverResponse->getBody());
        $response = $responseFactory->create($serverResponse->getBody(), $request);
        $users = $response->getReturnValues()[0]->getData();
        $this->assertCount(100, $users);
        // print_r($response->getReturnValues()[0]->getData());
       // $this->assertEquals('hello, world', );
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
    private function createUsers()
    {
        $users = [];
        foreach (range(1, 1) as $i) {
            $user = new User();
            $user->name = str_repeat('john', 1);
            $user->userId = $i;
            $user->address = new Address();
            $user->address->name = 'addr';
            $users[] = $user;
        }

        return [$users];
    }
}
