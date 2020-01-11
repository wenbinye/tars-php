<?php

declare(strict_types=1);

namespace wenbinye\tars\support\loadBalance;

interface LoadBalanceInterface
{
    public function __construct(array $hosts, array $weights);

    public function select();
}
