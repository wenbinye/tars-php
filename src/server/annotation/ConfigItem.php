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
     * factory 可以是 class_name::method 形式，也可以是 method(method 为当前类中的静态函数).
     *
     * @var string
     */
    public $factory;
}
