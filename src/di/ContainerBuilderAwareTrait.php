<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

trait ContainerBuilderAwareTrait
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function setContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;
    }
}
