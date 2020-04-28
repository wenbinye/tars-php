<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use kuiper\di\annotation\ComponentInterface;
use kuiper\di\annotation\ComponentTrait;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TarsServant implements ComponentInterface
{
    use ComponentTrait;

    /**
     * @var string
     */
    public $name;
}
