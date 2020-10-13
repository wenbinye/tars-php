<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class ReturnValue implements ReturnValueInterface
{
    /**
     * @var ?string
     */
    private $name;
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var ?string
     */
    private $payload;

    /**
     * ReturnValue constructor.
     *
     * @param string|null $name
     * @param mixed       $data
     * @param string|null $payload
     */
    public function __construct(?string $name, $data, ?string $payload)
    {
        $this->name = $name;
        $this->data = $data;
        $this->payload = $payload;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function isParameter(): bool
    {
        return !empty($this->name);
    }
}
