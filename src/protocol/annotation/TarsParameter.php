<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class TarsParameter
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
    /**
     * @Required()
     *
     * @var string
     */
    public $type;
    /**
     * @var int
     */
    public $order;
    /**
     * @var bool
     */
    public $required;
    /**
     * @var bool
     */
    public $out;
    /**
     * @var bool
     */
    public $routeKey;
}
