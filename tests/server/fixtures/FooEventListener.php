<?php

declare(strict_types=1);

namespace wenbinye\tars\server\fixtures;

class FooEventListener
{
    public $events;

    public function __invoke($event)
    {
        $this->events[] = $event;
    }
}
