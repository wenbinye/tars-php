<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

interface ContainerBuilderAwareInterface
{
    public function setContainerBuilder(ContainerBuilder $containerBuilder): void;
}
