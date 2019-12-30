<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface MethodMetadataFactoryInterface
{
    /**
     * 获取接口 ServantName, 参数，返回值等信息.
     *
     * @param object $client
     */
    public function create($client, string $method): MethodMetadata;
}
