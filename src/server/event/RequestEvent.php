<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use Swoole\Http\Request;
use Swoole\Http\Response;

class RequestEvent extends SwooleServerEvent
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}
