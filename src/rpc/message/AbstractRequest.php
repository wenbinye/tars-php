<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\tup\Tup;

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var object
     */
    protected $servant;
    /**
     * @var MethodMetadataInterface
     */
    protected $methodMetadata;
    /**
     * @var ParameterInterface[]
     */
    protected $parameters = [];
    /**
     * @var array
     */
    protected $attributes;

    public function isCurrentVersion(): bool
    {
        return Tup::VERSION === $this->getVersion();
    }

    public function getServant(): object
    {
        return $this->servant;
    }

    /**
     * @return ParameterInterface[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): MethodMetadataInterface
    {
        return $this->methodMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute(string $attribute, $value)
    {
        $copy = clone $this;
        $copy->attributes[$attribute] = $value;

        return $copy;
    }
}
