<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface MethodMetadataFactoryInterface
{
    public function create($client, string $method): MethodMetadata;
}
