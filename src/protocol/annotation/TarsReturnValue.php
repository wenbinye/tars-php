<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class TarsReturnValue
{
    /**
     * @var string
     */
    public $name = '';
    /**
     * @Required()
     *
     * @var string
     */
    public $type;
}
