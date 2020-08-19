<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message\tup;

use wenbinye\tars\rpc\message\RequestInterface;

/**
 * Class RequestPacket.
 *
 * context, status 区别：context 是两个服务之间用的，status 是从调用链的开始传到结束。
 * {@see https://github.com/TarsCloud/Tars/issues/729}
 */
class RequestPacket
{
    /**
     * @var int
     */
    private $version;
    /**
     * @var int
     */
    private $packetType;
    /**
     * @var int
     */
    private $messageType;
    /**
     * @var int
     */
    private $requestId;
    /**
     * @var string
     */
    private $servantName;
    /**
     * @var string
     */
    private $funcName;
    /**
     * @var string
     */
    private $buffer;
    /**
     * @var int
     */
    private $timeout;
    /**
     * @var array
     */
    private $context;
    /**
     * @var array
     */
    private $status;

    /**
     * RequestPacket constructor.
     */
    public function __construct(
        int $version,
        int $packetType,
        int $messageType,
        int $requestId,
        string $servantName,
        string $funcName,
        string $buffer,
        int $timeout,
        array $context,
        array $status)
    {
        $this->version = $version;
        $this->packetType = $packetType;
        $this->messageType = $messageType;
        $this->requestId = $requestId;
        $this->servantName = $servantName;
        $this->funcName = $funcName;
        $this->buffer = $buffer;
        $this->timeout = $timeout;
        $this->context = $context;
        $this->status = $status;
    }

    public static function parse(string $requestBody): RequestPacket
    {
        $unpackResult = \TUPAPI::decodeReqPacket($requestBody);

        return new self(
            (int) ($unpackResult['iVersion'] ?? Tup::VERSION),
            (int) ($unpackResult['cPacketType'] ?? Tup::PACKET_TYPE),
            (int) ($unpackResult['cMessageType'] ?? Tup::MESSAGE_TYPE),
            (int) ($unpackResult['iRequestId'] ?? -1),
            (string) ($unpackResult['sServantName'] ?? ''),
            (string) ($unpackResult['sFuncName'] ?? ''),
            (string) ($unpackResult['sBuffer'] ?? ''),
            (int) ($unpackResult['iTimeout'] ?? Tup::TIMEOUT),
            isset($unpackResult['context']) && is_array($unpackResult['context']) ? $unpackResult['context'] : [],
            isset($unpackResult['status']) && is_array($unpackResult['status']) ? $unpackResult['status'] : []
        );
    }

    public static function fromRequest(RequestInterface $request): RequestPacket
    {
        return new self(
            $request->getVersion(),
            $request->getPacketType(),
            $request->getMessageType(),
            $request->getRequestId(),
            $request->getServantName(),
            $request->getFuncName(),
            '',
            $request->getTimeout(),
            $request->getContext(),
            $request->getStatus()
        );
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getPacketType(): int
    {
        return $this->packetType;
    }

    public function getMessageType(): int
    {
        return $this->messageType;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function getFuncName(): string
    {
        return $this->funcName;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public static function builder(): RequestPacketBuilder
    {
        return new RequestPacketBuilder();
    }
}
