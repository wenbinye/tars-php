<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\fixtures\test;

class UserClient implements UserServant
{
    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        return [];
    }
}
