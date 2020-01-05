<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TarsServant
{
    /**
     * @var string
     */
    public $name;
}
