<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\lb;

interface LoadBalanceInterface
{
    /**
     * LoadBalanceInterface constructor.
     *
     * @param array $hosts
     * @param array $weights
     */
    public function __construct(array $hosts, array $weights);

    /**
     * @return mixed
     */
    public function select();
}
