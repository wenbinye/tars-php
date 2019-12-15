<?php

declare(strict_types=1);

namespace wenbinye\tars\server\annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class TaskHandler.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class TaskHandler
{
    /**
     * @var string
     *
     * @Required()
     */
    public $name;
}
