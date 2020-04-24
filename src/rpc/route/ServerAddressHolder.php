<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

class ServerAddressHolder implements ServerAddressHolderInterface, RefreshableServerAddressHolderInterface
{
    /**
     * @var Route
     */
    private $servantRoute;

    /**
     * @var int
     */
    private $pos;

    /**
     * RouteHolder constructor.
     */
    public function __construct(Route $route)
    {
        if ($this->servantRoute->isEmpty()) {
            throw new \InvalidArgumentException('No route for '.$route->getServantName());
        }
        $this->servantRoute = $route;
        $this->pos = 0;
    }

    public function get(): ServerAddress
    {
        return $this->servantRoute->getAddressList()[$this->pos];
    }

    public function refresh(bool $force = false): void
    {
        ++$this->pos;
        if ($this->pos >= $this->servantRoute->getSize()) {
            $this->pos = 0;
        }
    }
}
