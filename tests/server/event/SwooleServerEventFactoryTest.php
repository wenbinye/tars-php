<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use PHPUnit\Framework\TestCase;
use wenbinye\tars\server\SwooleEvent;
use wenbinye\tars\server\SwooleServer;

class SwooleServerEventFactoryTest extends TestCase
{
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|SwooleServer
     */
    private $server;
    /**
     * @var SwooleServerEventFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->server = $server = \Mockery::mock(SwooleServer::class);
        $this->factory = new SwooleServerEventFactory($server);
    }

    public function testCreate()
    {
        $event = $this->factory->create(SwooleEvent::START, []);
        $this->assertNotNull($event);
    }
}
