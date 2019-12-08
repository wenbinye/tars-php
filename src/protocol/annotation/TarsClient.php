<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class TarsClient
{
    /**
     * @var string
     */
    public $servant;
}
