<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class PackerV1Test extends TestCase
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

    /**
     * @var TypeParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->packer = new Packer(AnnotationReader::getInstance());
    }

    public function testName()
    {
        $s = "\x16\xbHello World";
        $type = $this->packer->parse('string');
        //$putString = \TUPAPI::putString(1, "Hello World", 1);
        $putString = \TUPAPI::getString((string) 1, $s, false, 1);
        // $result = $this->packer->unpack($type, '', $s, Version::TARS);
        // var_export([$putString]);
        $this->assertTrue(true);
    }
}
