<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

interface ComponentInterface
{
    public function process(): void;

    public function setClass(\ReflectionClass $class): void;
}
