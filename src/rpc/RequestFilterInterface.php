<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\message\ClientRequestInterface;

interface RequestFilterInterface
{
    /**
     * @param ClientRequestInterface $request
     *
     * @return ClientRequestInterface
     */
    public function filter(ClientRequestInterface $request): ClientRequestInterface;
}
