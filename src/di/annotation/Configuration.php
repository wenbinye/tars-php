<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

use wenbinye\tars\di\BeanConfigurationSourceAwareInterface;
use wenbinye\tars\di\BeanConfigurationSourceAwareTrait;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Configuration implements ComponentInterface, BeanConfigurationSourceAwareInterface
{
    use ComponentTrait;
    use BeanConfigurationSourceAwareTrait;

    public function process(): void
    {
        $this->beanConfigurationSource->addConfiguration($this->class->newInstance());
    }
}
