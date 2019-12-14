<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use Swoole\Http\Request;

class OpenEvent extends SwooleServerEvent
{
    /**
     * @var Request
     */
    private $request;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
