<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

trait ComponentTrait
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    public function setClass(\ReflectionClass $class): void
    {
        $this->class = $class;
    }

    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }
}
