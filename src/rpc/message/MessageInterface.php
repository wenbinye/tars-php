<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface MessageInterface
{
    public const TARS_VERSION = 1;
    public const TUP_VERSION = 3;

    public function getVersion(): int;

    public function getBody(): string;
}
