<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class PipeMessageEvent extends SwooleServerEvent
{
    /**
     * @var int
     */
    private $fromWorkerId;

    /**
     * @var mixed
     */
    private $message;

    public function getFromWorkerId(): int
    {
        return $this->fromWorkerId;
    }

    public function setFromWorkerId(int $fromWorkerId): void
    {
        $this->fromWorkerId = $fromWorkerId;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }
}
