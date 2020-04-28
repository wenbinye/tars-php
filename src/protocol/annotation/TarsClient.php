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
        $name = $this->value ?? $this->class->getName();
        $this->containerBuilder->addDefinitions([
            $name => factory([TarsClientFactoryInterface::class, 'create'])
                ->parameter('clientClassName', $this->class->getName()),
        ]);
    }
}
