<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface RequestInterface extends MessageInterface
{
    const PACKET_TYPE = 0;
    const MESSAGE_TYPE = 0;

    public function getServantName(): string;

    public function withServantName(string $servantName): self;

    public function getMethodName(): string;

    public function withMethodName(string $methodName): self;

    public function getRequestId(): int;

    public function withRequestId(int $requestId): self;

    public function getParameters(): array;

    public function withParameters(array $parameters): self;

    public function getMessageType(): int;

    public function getPacketType(): int;

    /**
     * @return mixed
     */
    public function getAttribute(string $attribute);

    /**
     * @param $value
     *
     * @return static
     */
    public function withAttribute(string $attribute, $value);
}
