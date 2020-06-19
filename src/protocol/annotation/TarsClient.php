<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use function DI\factory;
use kuiper\di\ContainerBuilderAwareInterface;
use kuiper\di\ContainerBuilderAwareTrait;
use wenbinye\tars\rpc\TarsClientFactoryInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TarsClient extends TarsServant implements ContainerBuilderAwareInterface
{
    use ContainerBuilderAwareTrait;

    /**
     * bean name.
     *
     * @var string
     */
    public $value;

    public function handle(): void
    {
        parent::handle();
        $name = $this->value ?? $this->class->getName();
        $this->containerBuilder->addDefinitions([
            $name => factory(function (TarsClientFactoryInterface $factory) {
                return $factory->create($this->class->getName());
            }),
        ]);
    }
}
