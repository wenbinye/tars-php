<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

interface CollectorInterface
{
    public function getPolicy(): string;

    public function getValues(): array;
}
