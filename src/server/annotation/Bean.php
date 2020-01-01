<?php

declare(strict_types=1);

namespace wenbinye\tars\server\annotation;

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
