<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface RequestInterface extends MessageInterface
{
    /**
     * Retrieve the servant name.
     */
    public function getServantName(): string;

    /**
     * Retrieve the function name.
     */
    public function getFuncName(): string;

    /**
     * Retrieve the request timeout.
     */
    public function getTimeout(): int;

    /**
     * Retrieve the request parameters.
     *
     * @return ParameterInterface[]
     */
    public function getParameters(): array;

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
     * Retrieve a single derived request attribute.
     *
     * @return mixed
     */
    public function getAttribute(string $attribute);

    /**
     * Return an instance with the specified derived request attribute.
     */
    public function withAttribute(string $attribute, $value);
}
