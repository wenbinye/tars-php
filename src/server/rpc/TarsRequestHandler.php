<?php

declare(strict_types=1);

namespace wenbinye\tars\server\rpc;

use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\ErrorCode;
use wenbinye\tars\rpc\message\ParameterInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\MiddlewareInterface;
use wenbinye\tars\rpc\MiddlewareSupport;
use wenbinye\tars\rpc\TarsRpcPacker;

class TarsRequestHandler implements RequestHandlerInterface
{
    use MiddlewareSupport;

    /**
     * @var TarsRpcPacker
     */
    private $packer;

    /**
     * TarsRequestHandler constructor.
     *
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(PackerInterface $packer, array $middlewares = [])
    {
        $this->packer = new TarsRpcPacker($packer);
        $this->middlewares = $middlewares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildMiddlewareStack([$this, 'invoke'])->__invoke($request);
    }

    public function invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (null === $request->getServant()) {
            return new ServerResponse($request, [], ErrorCode::SERVER_NO_SERVANT_ERR);
        }
        if (empty($request->getMethod()->getMethodName())) {
            return new ServerResponse($request, [], ErrorCode::SERVER_NO_FUNC_ERR);
        }
        $parameters = array_map(static function (ParameterInterface $parameter) {
            return $parameter->isOut() ? null : $parameter->getData();
        }, $request->getParameters());
        $parameters[] = call_user_func_array([$request->getServant(), $request->getFuncName()], $parameters);

        return new ServerResponse($request, $this->packer->packResponse($request->getMethod(), $parameters, $request->getVersion()),
            ErrorCode::SERVER_SUCCESS);
    }
}
