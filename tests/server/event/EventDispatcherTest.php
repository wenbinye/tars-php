<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherTest extends TestCase
{
    public function testLazyListener(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('foo', [function () {
            var_export(func_get_args());

            return new EventListener();
        }]);
        $dispatcher->dispatch((object) ['event' => 'foo'], 'foo');
    }
}

class EventListener
{
    public function __invoke($event)
    {
        var_export($event);
    }
}
