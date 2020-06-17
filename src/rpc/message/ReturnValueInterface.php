<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ReturnValueInterface
{
    /**
     * Checks the data is return by output parameter.
     */
    public function isParameter(): bool;

    /**
     * Gets the name of the output parameter.
     */
    public function getName(): ?string;

    /**
     * Gets the php type value.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Gets the packet data.
     */
    public function getPayload(): ?string;
}
