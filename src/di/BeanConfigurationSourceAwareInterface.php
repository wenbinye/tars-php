<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

interface BeanConfigurationSourceAwareInterface
{
    public function setBeanConfigurationSource(BeanConfigurationSource $beanConfigurationSource): void;
}
