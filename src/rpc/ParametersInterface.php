<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface ParametersInterface
{
    public function getScheme(): string;

    public function getHost(): string;

    public function getPort(): int;

    public function __toString();
}
