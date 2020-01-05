<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface TarsClientGeneratorInterface
{
    public function generate(string $clientClassName): string;
}
