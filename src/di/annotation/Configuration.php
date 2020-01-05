<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

use wenbinye\tars\di\ContainerBuilderAwareInterface;
use wenbinye\tars\di\ContainerBuilderAwareTrait;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Configuration implements ComponentInterface, ContainerBuilderAwareInterface
{
    use ComponentTrait;
    use ContainerBuilderAwareTrait;

    public function process(): void
    {
        $this->containerBuilder->addConfiguration($this->class->newInstance());
    }
}
