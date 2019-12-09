<?php

declare(strict_types=1);

namespace wenbinye\tars\support;

use Psr\Container\ContainerInterface;

interface ContainerFactoryInterface
{
    public function create(): ContainerInterface;
}
