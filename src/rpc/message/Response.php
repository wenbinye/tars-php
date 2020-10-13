<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\tup\ResponsePacket;

/**
 * version=3 TUPAPI::decode 返回：
 * ```
 * [
 *    "buf" => "\x08\f",
 *    "iRequestId" => 1,
 *    "iRet" => 0,
 *    "sBuffer" => "\x08\f",
 *    "sFuncName" => "",
 *    "sServantName" => "",
 *    "status" => 0,
 * ]
 * ```.
 *
 * version=1 TUPAPI::decode 返回：
 * ```
 * [
 *    "cPacketType" => 1,
 *    "iMessageType" => 1,
 *    "iRequestId" => 1,
 *    "iRet" => 0,
 *    "iVersion" => 1,
 *    "sBuffer" => "\x06\vhello world",
 *    "sResultDesc" => "",
 * ]
 * ```
 *
 * Class Response
 */
class Response extends AbstractResponse
{
    /**
     * @var ResponsePacket
     */
    private $responsePacket;

    /**
     * Response constructor.
     */
    public function __construct(ResponsePacket $responsePacket, RequestInterface $request, array $returnValues)
    {
        $this->responsePacket = $responsePacket;
        $this->request = $request;
        $this->returnValues = $returnValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): int
    {
        return $this->responsePacket->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketType(): int
    {
        return $this->responsePacket->getPacketType();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType(): int
    {
        return $this->responsePacket->getMessageType();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestId(): int
    {
        return $this->responsePacket->getRequestId();
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        return $this->responsePacket->getBuffer();
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnCode(): int
    {
        return $this->responsePacket->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(): string
    {
        return $this->responsePacket->getResultDesc()
            ?? ErrorCode::fromValue($this->responsePacket->getResultCode(),
                ErrorCode::fromValue(ErrorCode::UNKNOWN))->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        return $this->responsePacket->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): array
    {
        return $this->responsePacket->getContext();
    }
}
