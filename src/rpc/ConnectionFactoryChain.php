<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class ConnectionFactoryChain implements ConnectionFactoryInterface
{
    /**
     * @var ConnectionFactoryInterface[]
     */
    private $connectionFactories;

    public function __construct(array $connectionFactories)
    {
        $this->connectionFactories = $connectionFactories;
    }

    public function has(string $servantName): bool
    {
        foreach ($this->connectionFactories as $factory) {
            if ($factory->has($servantName)) {
                return true;
            }
        }

        return false;
    }

    public function create(string $servantName): ConnectionInterface
    {
        foreach ($this->connectionFactories as $factory) {
            if ($factory->has($servantName)) {
                return $factory->create($servantName);
            }
        }
        throw new \InvalidArgumentException('Cannot create connection for '.$servantName);
    }
}
