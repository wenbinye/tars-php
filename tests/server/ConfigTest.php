<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testParse()
    {
        $result = Config::parse('<app>
<server>
foo=1
</server>
<client>
bar=2
</client>
</app>');
        $this->assertEquals(['app' => [
            'server' => ['foo' => '1'],
            'client' => ['bar' => '2'],
        ]], $result->toArray());
    }

    public function testParseFile()
    {
        $result = Config::parseFile(__DIR__.'/fixtures/PHPTest.PHPHttpServer.config.conf');
        var_export($result->toArray());
    }
}
