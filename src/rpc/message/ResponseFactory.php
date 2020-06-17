<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\tup\ResponsePacket;
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
        $responsePacket = ResponsePacket::parse($response, $request->getVersion());
        $requestId = $responsePacket->getRequestId();
        if ($requestId > 0 && $requestId !== $request->getRequestId()) {
            throw new \InvalidArgumentException("request id not match, got {$requestId}, expected ".$request->getRequestId());
        }
        $returnValues = [];
        if (ErrorCode::SERVER_SUCCESS === $responsePacket->getResultCode()) {
            $returnValues = $this->packer->unpackResponse(
                $request->getMethod(),
                $responsePacket->getBuffer(),
                $responsePacket->getVersion());
        }

        return new Response($responsePacket, $request, $returnValues);
    }
}
