<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;

interface MethodMetadataInterface
{
    public function getNamespace(): string;

    public function getClassName(): string;

    public function getMethodName(): string;

    public function getServantName(): string;

    /**
     * @return TarsParameter[]
     */
    public function getParameters(): array;

    public function getReturnType(): ?TarsReturnType;
}
