<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class TarsClientFactory implements TarsClientFactoryInterface
{
    /**
     * @var TarsClient
     */
    private $tarsClient;
    /**
     * @var TarsClientGeneratorInterface
     */
    private $tarsClientGenerator;

    public function __construct(TarsClient $tarsClient, TarsClientGeneratorInterface $tarsClientGenerator)
    {
        $this->tarsClient = $tarsClient;
        $this->tarsClientGenerator = $tarsClientGenerator;
    }

    public function create(string $clientClassName)
    {
        $generatedClass = $this->tarsClientGenerator->generate($clientClassName);

        return new $generatedClass($this->tarsClient);
    }
}
