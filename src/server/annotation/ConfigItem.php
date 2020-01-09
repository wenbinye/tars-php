<?php

declare(strict_types=1);

namespace wenbinye\tars\server\annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ConfigItem
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $factory;
}
