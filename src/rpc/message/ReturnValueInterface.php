<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ReturnValueInterface
{
    public function isParameter(): bool;

    public function getName(): string;

    public function getData();

    public function getPayload(): string;
}
