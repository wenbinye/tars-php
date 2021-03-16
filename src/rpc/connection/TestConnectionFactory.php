<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\connection;

use kuiper\annotations\AnnotationReader;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\message\RequestInterface;
use wenbinye\tars\rpc\message\ServerResponse;
use wenbinye\tars\rpc\TarsRpcPacker;

class TestConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var array
     */
    private $responses;

    /**
     * @var TarsRpcPacker
     */
    private $rpcPacker;

    public function __construct()
    {
        $this->rpcPacker = new TarsRpcPacker(new Packer(AnnotationReader::getInstance()));
    }

    public function pushResponse(array $returnValues): void
    {
        $this->responses[] = $returnValues;
    }

    public function popResponse(RequestInterface $request): string
    {
        $returnValues = array_pop($this->responses);
        if (null === $returnValues) {
            throw new \InvalidArgumentException('Request response not push');
        }

        return (new ServerResponse($request,
            $this->rpcPacker->packResponse($request->getMethod(), $returnValues, $request->getVersion())))
            ->getBody();
    }

    public function create(string $servantName): ConnectionInterface
    {
        return new TestConnection([$this, 'popResponse']);
    }
}
