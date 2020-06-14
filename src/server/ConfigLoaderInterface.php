<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Input\InputInterface;

interface ConfigLoaderInterface
{
    public function load(InputInterface $input): void;
}
