<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface MessageInterface
{
    /**
     * Gets the TUP version.
     */
    public function getVersion(): int;

    /**
     * Retrieve the request packet type.
     */
    public function getPacketType(): int;

    /**
     * Retrieve the request message type.
     */
    public function getMessageType(): int;

    /**
     * Retrieve the unique request id.
     */
    public function getRequestId(): int;

    public function getStatus(): array;

    public function getContext(): array;

    /**
     * Gets the request or response packet content.
     */
    public function getBody(): string;
}
