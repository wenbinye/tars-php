<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ResponseFactoryInterface
{
    public function create(string $response, RequestInterface $request): ResponseInterface;
}
