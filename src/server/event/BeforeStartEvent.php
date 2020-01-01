<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

/**
 * This is virtual server event, use to run code before create
 * swoole server.
 *
 * Class BeforeStartEvent
 */
class BeforeStartEvent extends SwooleServerEvent
{
}
