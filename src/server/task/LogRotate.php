<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

class LogRotate
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * LogRotate constructor.
     */
    public function __construct(string $suffix = '-Ymd')
    {
        $this->suffix = $suffix;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }
}
