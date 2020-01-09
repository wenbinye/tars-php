<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use function DI\factory;
use kuiper\di\annotation\ComponentInterface;
use kuiper\di\annotation\ComponentTrait;
use kuiper\di\ContainerBuilderAwareInterface;
use kuiper\di\ContainerBuilderAwareTrait;
use wenbinye\tars\rpc\TarsClientFactoryInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TarsClient extends TarsServant implements ComponentInterface, ContainerBuilderAwareInterface
{
    use ComponentTrait;
    use ContainerBuilderAwareTrait;

    /**
     * @var string
     */
    public $name;

    public function handle(): void
    {
        $name = $this->class->getName();
        $this->containerBuilder->addDefinitions([
            $name => factory([TarsClientFactoryInterface::class, 'create'])
                ->parameter('clientClassName', $name),
        ]);
    }
}
