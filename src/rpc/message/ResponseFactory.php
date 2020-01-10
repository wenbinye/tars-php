<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\TarsRpcPacker;

class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var TarsRpcPacker
     */
    private $packer;

    public function __construct(PackerInterface $packer)
    {
        $this->packer = new TarsRpcPacker($packer);
    }

    public function create(string $response, RequestInterface $request): ResponseInterface
    {
        $parsedBody = \TUPAPI::decode($response, $request->getVersion());
        $returnCode = $parsedBody['iRet'] ?? ErrorCode::UNKNOWN;
        $returnValues = [];

        if (ErrorCode::SERVER_SUCCESS === $returnCode) {
            $returnValues = $this->packer->unpackResponse($request->getMethod(), $parsedBody['sBuffer'], $request->getVersion());
        }

        return new Response($request, $response, $returnCode, $returnValues);
    }
}
