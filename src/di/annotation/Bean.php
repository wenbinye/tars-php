<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Bean
{
    /**
     * @var string
     */
    public $name;
}
