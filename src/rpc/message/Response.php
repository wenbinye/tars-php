<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

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
class Response implements ResponseInterface
{
    use ResponseTrait;

    /**
     * Response constructor.
     */
    public function __construct(RequestInterface $request, string $body, int $returnCode, array $returnValues)
    {
        $this->body = $body;
        $this->request = $request;
        $this->returnCode = $returnCode;
        $this->returnValues = $returnValues;
    }
}
