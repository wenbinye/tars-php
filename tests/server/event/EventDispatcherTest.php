<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherTest extends TestCase
{
    public function testLazyListener(): void
    {
        $args = [];
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('foo', [function () use (&$args) {
            $args[] = func_get_args();

            return $args[] = new EventListener();
        }]);
        $dispatcher->dispatch((object) ['event' => 1], 'foo');
        $dispatcher->dispatch((object) ['event' => 2], 'foo');
        // $this->assertEquals(1, count($args));
        // var_export($args);
        $this->assertEquals(2, count($args[1]->events));
    }
}

class EventListener
{
    public $events;

    public function __invoke($event)
    {
        $this->events[] = $event;
    }
}
