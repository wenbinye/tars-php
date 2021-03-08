<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\fixtures\RequestV1;
use wenbinye\tars\protocol\fixtures\RequestV2;

class ApiCompatibleTest extends TestCase
{
    const VERSION = Version::TUP;
    const REQUEST_ID = 1;
    const SERVANT_NAME = 'test.test.test';
    const FUNC_NAME = 'example';
    const PACKET_TYPE = 0;
    const MESSAGE_TYPE = 0;
    const TIMEOUT = 2;

    const ARG_NAME = 'arg';

    /**
     * @var Packer
     */
    private $packer;

    protected function setUp(): void
    {
        $this->packer = new Packer(AnnotationReader::getInstance());
    }

    public function testName()
    {
        $namespace = __NAMESPACE__.'\\fixtures';
        $type = 'RequestV1';
        $data = new RequestV1();
        $data->id = 10;
        $data->page = 3;
        $payload = $this->packer->pack($this->packer->parse($type, $namespace), self::ARG_NAME, $data, self::VERSION);

        $buffer = Packer::toPayload(self::ARG_NAME, $payload);
        $typeV2 = 'RequestV2';
        $result = $this->packer->unpack($this->packer->parse($typeV2, $namespace), self::ARG_NAME, $buffer, self::VERSION);
        $this->assertInstanceOf(RequestV2::class, $result);
    }
}
