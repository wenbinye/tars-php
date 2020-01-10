<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ParameterInterface
{
    /**
     * Retrieve the parameter position order.
     */
    public function getOrder(): int;

    /**
     * Retrieve the parameter name.
     */
    public function getName(): string;

    /**
     * Whether it's output.
     */
    public function isOut(): bool;

    /**
     * Retrieve the parameter packed data.
     */
    public function getPayload(): string;

    /**
     * Retrieve the parameter original data.
     *
     * @return mixed
     */
    public function getData();
}
