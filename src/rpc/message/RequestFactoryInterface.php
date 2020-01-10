<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface RequestFactoryInterface
{
    /**
     * Create the request.
     *
     * @param object $servant
     */
    public function createRequest($servant, string $method, array $parameters): RequestInterface;
}
