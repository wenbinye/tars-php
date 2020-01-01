<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface MessageInterface
{
    const TARS_VERSION = 1;
    const TUP_VERSION = 3;

    public function getVersion(): int;

    public function getBody(): string;
}
