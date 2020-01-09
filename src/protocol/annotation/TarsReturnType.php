<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class TarsReturnType
{
    /**
     * @Required()
     *
     * @var string
     */
    public $type;
}
