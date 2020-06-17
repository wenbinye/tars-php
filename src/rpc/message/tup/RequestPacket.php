<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message\tup;

/**
 * Class RequestPacket.
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
            $unpackResult['iVersion'] ?? Tup::VERSION,
            $unpackResult['cPacketType'] ?? Tup::PACKET_TYPE,
            $unpackResult['cMessageType'] ?? Tup::MESSAGE_TYPE,
            $unpackResult['iRequestId'] ?? -1,
            $unpackResult['sServantName'] ?? '',
            $unpackResult['sFuncName'] ?? '',
            $unpackResult['sBuffer'] ?? '',
            $unpackResult['iTimeout'] ?? Tup::TIMEOUT,
            $unpackResult['context'] ?? [],
            $unpackResult['status'] ?? []
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
