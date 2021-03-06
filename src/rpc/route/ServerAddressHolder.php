<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

/**
 * Class ServerAddressHolder.
 */
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
        if ($route->isEmpty()) {
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
