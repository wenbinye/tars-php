<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\rpc\message\ResponseInterface;

interface ErrorHandlerInterface
{
    /**
     * 处理异常响应.
     *
     * @return mixed
     */
    public function handle(ResponseInterface $response);
}
