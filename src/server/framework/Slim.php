<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Psr\Container\ContainerInterface;
use wenbinye\tars\support\ContainerFactoryInterface;

class Slim implements ContainerFactoryInterface
{
    public function create(): ContainerInterface
    {
        $containerFactory = new PhpDiContainerFactory();

        return $containerFactory->create();
    }
}
