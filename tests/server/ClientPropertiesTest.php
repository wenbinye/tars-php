<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use PHPUnit\Framework\TestCase;

class ClientPropertiesTest extends TestCase
{
    public function testFromConfig()
    {
        $reflectionClass = new \ReflectionClass(ClientProperties::class);
        foreach ($reflectionClass->getMethods() as $method) {
            $reflectionType = $method->getReturnType();
            echo $method->getName(), ' ', $reflectionType, "\n";
        }
    }
}
