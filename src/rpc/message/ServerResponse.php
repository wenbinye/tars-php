<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ResponseTrait;
use wenbinye\tars\rpc\message\ReturnValueInterface;

class ServerResponse implements ResponseInterface
{
    use ResponseTrait;

    public function __construct(RequestInterface $request, array $returnValues, int $returnCode)
    {
        $this->request = $request;
        $this->returnValues = $returnValues;
        $this->returnCode = $returnCode;
    }

    public function getBody(): string
    {
        if (self::TUP_VERSION === $this->getVersion()) {
            return \TUPAPI::encode($this->getVersion(),
                $this->request->getRequestId(),
                $this->request->getServantName(),
                $this->request->getFuncName(),
                $this->request->getPacketType(),
                $this->request->getMessageType(),
                0,
                [],
                ['STATUS_RESULT_CODE' => $this->getReturnCode()],
                $this->getReturnValueArray());
        }

        return \TUPAPI::encodeRspPacket($this->getVersion(),
            $this->request->getPacketType(),
            $this->request->getMessageType(),
            $this->request->getRequestId(),
            $this->getReturnCode(),
            $this->getMessage(),
            $this->getReturnValueArray(),
            []);
    }

    private function getReturnValueArray(): array
    {
        $ret = [];
        /** @var ReturnValueInterface $returnValue */
        foreach ($this->returnValues as $returnValue) {
            $ret[$returnValue->getName()] = $returnValue->getPayload();
        }

        return self::TUP_VERSION === $this->getVersion() ? $ret : array_values($ret);
    }
}
