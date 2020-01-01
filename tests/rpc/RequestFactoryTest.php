<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\protocol\TypeParser;

class RequestFactoryTest extends TestCase
{
    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \wenbinye\tars\protocol\exception\SyntaxErrorException
     */
    public function testEncode()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $packer = new Packer(new TarsTypeFactory($reader));
        $parser = new TypeParser();
        $factory = new RequestFactory(new RequestIdGenerator());
        $payload['arg'] = $packer->pack($parser->parse('int', ''), 'arg', 3, 3);
        $request = $factory->createRequest('fooService', 'foo', $payload);
        $requestBody = $request->encode();
        $unpackResult = \TUPAPI::decodeReqPacket($requestBody);
        var_export($unpackResult);
        // iRequestId
        // iVersion
        // sServantName
        // sFuncName
        // sBuffer
    }
}
