<?php

declare(strict_types=1);

namespace wenbinye\tars\registry;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TypeParser;
use wenbinye\tars\rpc\ConnectionInterface;
use wenbinye\tars\rpc\RequestFactoryInterface;

class QueryClient
{
    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var TypeParser
     */
    private $parser;

    public function findObjectById(string $id)
    {
        $payload['id'] = $this->packer->pack('id', $id, $this->parser->parse('string', __NAMESPACE__));
        $request = $this->requestFactory->createRequest($payload);
        $response = $this->connection->send($request);
        $decode = \TUPAPI::decode($response, $request->getVersion());
        if (0 !== $decode['iRet']) {
        }

        return $this->packer->unpack('', $decode['sBuffer'], $this->parser->parse('vector<EndpointF>', __NAMESPACE__));
    }
}
