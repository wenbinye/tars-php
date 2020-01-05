<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\Definition\Source\Autowiring;

interface AutowiringAwareInterface
{
    public function setAutowiring(Autowiring $autowiring): void;
}
