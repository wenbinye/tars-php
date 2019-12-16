<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

class DeleteFileTask
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * Milliseconds delay to delete file.
     *
     * @var int
     */
    private $delay;

    /**
     * DeleteFileTask constructor.
     */
    public function __construct(string $fileName, int $delay)
    {
        $this->fileName = $fileName;
        $this->delay = $delay;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }
}
