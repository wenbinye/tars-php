<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Output\OutputInterface;

interface ServerInterface
{
    public function start(): void;

    public function stop(): void;

    public function setOutput(OutputInterface $output): void;
}
