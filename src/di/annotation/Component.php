<?php

declare(strict_types=1);

namespace wenbinye\tars\di\annotation;

use function DI\get;
use wenbinye\tars\di\ContainerBuilderAwareInterface;
use wenbinye\tars\di\ContainerBuilderAwareTrait;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Component implements ComponentInterface, ContainerBuilderAwareInterface
{
    use ComponentTrait;
    use ContainerBuilderAwareTrait;

    /**
     * @var string
     */
    public $value = '';

    public function process(): void
    {
        if (!empty($this->value)) {
            $names = [$this->value];
        } else {
            $names = $this->getBeanNames();
        }
        $definitions = [];
        foreach ($names as $name) {
            $definitions[$name] = get($this->class->getName());
        }
        $this->containerBuilder->addDefinitions($definitions);
    }

    protected function getBeanNames(): array
    {
        return [$this->class->getName()];
    }
}
