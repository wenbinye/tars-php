<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

interface ConfigLoaderInterface
{
    public function load(string $configFile, array $properties = []): void;
}
