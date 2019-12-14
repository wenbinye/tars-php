<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class PacketEvent extends SwooleServerEvent
{
    /**
     * @var string
     */
    private $data;
    /**
     * @var array
     */
    private $clientInfo;

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getClientInfo(): array
    {
        return $this->clientInfo;
    }

    public function setClientInfo(array $clientInfo): void
    {
        $this->clientInfo = $clientInfo;
    }
}
