<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

interface ClientRequestInterface extends RequestInterface
{
    /**
     * Change request context.
     *
     * @return static
     */
    public function withContext(array $context);

    /**
     * Change request status.
     *
     * @return static
     */
    public function withStatus(array $status);
}
