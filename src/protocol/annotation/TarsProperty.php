<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class TarsProperty
{
    /**
     * @Required()
     *
     * @var string
     */
    public $type;

    /**
     * @Required()
     *
     * @var int
     */
    public $order;

    /**
     * @var bool
     */
    public $required;
}
