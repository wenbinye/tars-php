<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\server;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\rpc\message\ParameterInterface;
use wenbinye\tars\rpc\message\ResponseInterface;
use wenbinye\tars\rpc\message\ServerRequestInterface;
use wenbinye\tars\rpc\message\ServerResponse;
use wenbinye\tars\rpc\MiddlewareSupport;
use wenbinye\tars\rpc\TarsRpcPacker;

class TarsRequestHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    use MiddlewareSupport;

    /**
     * @var TarsRpcPacker
     */
    private $tarsRpcPacker;
    /**
     * @var ErrorHandlerInterface|null
     */
    private $errorHandler;

    public function __construct(PackerInterface $packer, ErrorHandlerInterface $errorHandler, ?LoggerInterface $logger, array $middlewares = [])
    {
        $this->tarsRpcPacker = new TarsRpcPacker($packer);
        $this->setLogger($logger ?? new NullLogger());
        $this->errorHandler = $errorHandler;
        $this->middlewares = $middlewares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->buildMiddlewareStack([$this, 'invoke'])->__invoke($request);
    }

    public function invoke(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = array_map(static function (ParameterInterface $parameter) {
            return $parameter->isOut() ? null : $parameter->getData();
        }, $request->getParameters());
        try {
            $parameters[] = call_user_func_array([$request->getServant(), $request->getFuncName()], $parameters);

            return new ServerResponse(
                $request,
                $this->tarsRpcPacker->packResponse($request->getMethod(), $parameters, $request->getVersion())
            );
        } catch (\Throwable $e) {
            return $this->errorHandler->handle($request, $e);
        }
    }
}
