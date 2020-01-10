<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\MethodMetadata;

interface MethodMetadataFactoryInterface
{
    /**
     * 获取接口 ServantName, 参数，返回值等信息.
     *
     * @param object $servant
     * @return MethodMetadata
     */
    public function create($servant, string $method): MethodMetadata;
}
