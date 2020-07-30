<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ClientRequestFactoryInterface
{
    /**
     * Create the request.
     *
     * @param object $servant
     */
    public function createRequest($servant, string $method, array $parameters): ClientRequestInterface;
}
