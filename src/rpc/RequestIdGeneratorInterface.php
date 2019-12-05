<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface RequestIdGeneratorInterface
{
    public function generate(): int;
}
