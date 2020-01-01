<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class TarsServant
{
    /**
     * @var string
     */
    public $servant;
}
