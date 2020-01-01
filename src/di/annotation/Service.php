<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Service extends Component
{
    protected function getBeanNames(): array
    {
        return $this->getClass()->getInterfaceNames() ?: parent::getBeanNames();
    }
}
