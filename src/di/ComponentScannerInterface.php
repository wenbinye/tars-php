<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

interface ComponentScannerInterface
{
    public function scan(array $namespaces): void;
}
