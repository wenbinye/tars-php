<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface RequestIdGeneratorInterface
{
    public function generate(): int;
}
