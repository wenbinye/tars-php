<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): void;
}
