<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class Parameter implements ParameterInterface
{
    /**
     * @var int
     */
    private $order;
    /**
     * @var string
     */
    private $name;
    /**
     * @var bool
     */
    private $out;
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var ?string
     */
    private $payload;

    public function __construct(int $order, string $name, bool $out, $data, ?string $payload)
    {
        $this->order = $order;
        $this->name = $name;
        $this->out = $out;
        $this->data = $data;
        $this->payload = $payload;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isOut(): bool
    {
        return $this->out;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }
}
