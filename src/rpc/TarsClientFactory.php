<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class TarsClientFactory implements TarsClientFactoryInterface
{
    /**
     * @var TarsClientInterface
     */
    private $tarsClient;
    /**
     * @var ServantProxyGeneratorInterface
     */
    private $tarsClientGenerator;

    public function __construct(TarsClientInterface $tarsClient, ServantProxyGeneratorInterface $tarsClientGenerator)
    {
        $this->tarsClient = $tarsClient;
        $this->tarsClientGenerator = $tarsClientGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $clientClassName)
    {
        $generatedClass = $this->tarsClientGenerator->generate($clientClassName);

        return new $generatedClass($this->tarsClient);
    }
}
