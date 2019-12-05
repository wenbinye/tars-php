<?php

namespace wenbinye\tars\protocol\annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class TarsStructProperty
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $order;

    /**
     * @var bool
     */
    public $required;

    /**
     * @var string
     */
    public $type;
}
