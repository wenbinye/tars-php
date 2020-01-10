<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\rpc\message\ResponseInterface;

interface StatInterface
{
    public function success(ResponseInterface $response, int $responseTime): void;

    public function fail(ResponseInterface $response, int $responseTime): void;

    public function timedOut(ResponseInterface $response, int $responseTime): void;

    public function send(): void;
}
