<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

interface ServerInterface
{
    public function start(): void;

    public function stop(): void;
}
