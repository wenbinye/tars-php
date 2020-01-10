<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface RequestInterface extends MessageInterface
{
    public const PACKET_TYPE = 0;
    public const MESSAGE_TYPE = 0;
    public const DEFAULT_TIMEOUT = 2000;

    /**
     * Retrieve current servant object.
     *
     * @return object
     */
    public function getServant();

    /**
     * Retrieve current method metadata.
     */
    public function getMethod(): MethodMetadataInterface;

    /**
     * Retrieve the servant name.
     */
    public function getServantName(): string;

    /**
     * Retrieve the function name.
     */
    public function getFuncName(): string;

    /**
     * Retrieve the unique request id.
     *
     * @return string
     */
    public function getRequestId(): int;

    /**
     * Retrieve the request parameters.
     *
     * @return ParameterInterface[]
     */
    public function getParameters(): array;

    /**
     * Return an instance with the specified request parameters.
     *
     * @param ParameterInterface[] $parameters
     *
     * @return static
     */
    public function withParameters(array $parameters);

    /**
     * Retrieve the request message type.
     */
    public function getMessageType(): int;

    /**
     * Retrieve the request packet type.
     */
    public function getPacketType(): int;

    /**
     * Retrieve the request timeout.
     */
    public function getTimeout(): int;

    /**
     * Retrieve a single derived request attribute.
     *
     * @return mixed
     */
    public function getAttribute(string $attribute);

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @return static
     */
    public function withAttribute(string $attribute, $value);
}
