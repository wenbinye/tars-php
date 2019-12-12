<?php

declare(strict_types=1);

namespace wenbinye\tars\server\annotation;

use Doctrine\Common\Annotations\Annotation\Target;

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
