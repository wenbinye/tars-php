<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\exception;

use wenbinye\tars\rpc\message\ResponseInterface;

class ServerException extends \Exception
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * ServerException constructor.
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response->getMessage(), $response->getReturnCode());
        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
