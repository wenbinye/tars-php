<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

trait BeanConfigurationSourceAwareTrait
{
    /**
     * @var BeanConfigurationSource
     */
    private $beanConfigurationSource;

    public function setBeanConfigurationSource(BeanConfigurationSource $beanConfigurationSource): void
    {
        $this->beanConfigurationSource = $beanConfigurationSource;
    }
}
