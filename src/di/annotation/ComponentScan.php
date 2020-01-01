<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ComponentScan
{
    /**
     * @var string[]
     */
    public $basePackages = [];
}
