<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\exception\InvalidMethodException;

interface MethodMetadataFactoryInterface
{
    /**
     * 获取接口 ServantName, 参数，返回值等信息.
     *
     * @param object $servant
     *
     * @throws InvalidMethodException 如果方法未找到
     */
    public function create($servant, string $method): MethodMetadata;
}
