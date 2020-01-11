<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

class RequestIdGenerator implements RequestIdGeneratorInterface
{
    /**
     * @var int
     */
    private $id;

    public function __construct($start = 1)
    {
        $this->id = $start;
    }

    public function generate(): int
    {
        return $this->id++;
    }
}
