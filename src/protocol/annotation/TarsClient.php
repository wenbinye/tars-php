<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use function DI\factory;
use wenbinye\tars\di\annotation\ComponentInterface;
use wenbinye\tars\di\annotation\ComponentTrait;
use wenbinye\tars\di\ContainerBuilderAwareInterface;
use wenbinye\tars\di\ContainerBuilderAwareTrait;
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

    public function process(): void
    {
        $name = $this->class->getName();
        $this->containerBuilder->addDefinitions([
            $name => factory([TarsClientFactoryInterface::class, 'create'])
                ->parameter('clientClassName', $name),
        ]);
    }
}
