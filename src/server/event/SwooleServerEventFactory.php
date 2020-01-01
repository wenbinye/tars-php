<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

use Swoole\Server;
use Swoole\WebSocket\Frame;
use wenbinye\tars\server\SwooleServer;

class SwooleServerEventFactory
{
    /**
     * @var SwooleServer
     */
    private $server;

    /**
     * SwooleServerEventFactory constructor.
     */
    public function __construct(SwooleServer $server)
    {
        $this->server = $server;
    }

    public function create(string $eventName, array $args): ?SwooleServerEvent
    {
        $method = sprintf('create%sEvent', $eventName);
        if (method_exists($this, $method)) {
            /** @var SwooleServerEvent $event */
            $event = $this->$method(...$args);
            $event->setServer($this->server);
            if (!empty($args) && $args[0] instanceof Server) {
                $event->setSwooleServer($args[0]);
            }

            return $event;
        }

        return null;
    }

    public function createBeforeStartEvent(): BeforeStartEvent
    {
        return new BeforeStartEvent();
    }

    public function createStartEvent(): StartEvent
    {
        return new StartEvent();
    }

    public function createShutdownEvent(): ShutdownEvent
    {
        return new ShutdownEvent();
    }

    public function createManagerStartEvent(): ManagerStartEvent
    {
        return new ManagerStartEvent();
    }

    public function createManagerStopEvent(): ManagerStopEvent
    {
        return new ManagerStopEvent();
    }

    public function createWorkerStartEvent($server, int $workerId): WorkerStartEvent
    {
        $event = new WorkerStartEvent();
        $event->setWorkerId($workerId);

        return $event;
    }

    public function createWorkerStopEvent($server, int $workerId): WorkerStopEvent
    {
        $event = new WorkerStopEvent();
        $event->setWorkerId($workerId);

        return $event;
    }

    public function createWorkerExitEvent($server, int $workerId): WorkerExitEvent
    {
        $event = new WorkerExitEvent();
        $event->setWorkerId($workerId);

        return $event;
    }

    public function createWorkerErrorEvent($server, int $workerId, int $workerPid, int $exitCode): WorkerErrorEvent
    {
        $event = new WorkerErrorEvent();
        $event->setWorkerId($workerId);
        $event->setWorkerPid($workerPid);
        $event->setExitCode($exitCode);

        return $event;
    }

    public function createConnectEvent($server, int $fd, int $reactorId): ConnectEvent
    {
        $event = new ConnectEvent();
        $event->setFd($fd);
        $event->setReactorId($reactorId);

        return $event;
    }

    public function createCloseEvent($server, int $fd, int $reactorId): CloseEvent
    {
        $event = new CloseEvent();
        $event->setFd($fd);
        $event->setReactorId($reactorId);

        return $event;
    }

    public function createRequestEvent($request, $response): RequestEvent
    {
        $event = new RequestEvent();
        $event->setRequest($request);
        $event->setResponse($response);

        return $event;
    }

    public function createReceiveEvent($server, int $fd, int $reactorId, string $data): ReceiveEvent
    {
        $event = new ReceiveEvent();
        $event->setFd($fd);
        $event->setReactorId($reactorId);
        $event->setData($data);

        return $event;
    }

    public function createPacketEvent($server, string $data, array $clientInfo): PacketEvent
    {
        $event = new PacketEvent();
        $event->setData($data);
        $event->setClientInfo($clientInfo);

        return $event;
    }

    public function createTaskEvent($server, int $taskId, int $fromWorkerId, $data): TaskEvent
    {
        $event = new TaskEvent();
        $event->setTaskId($taskId);
        $event->setFromWorkerId($fromWorkerId);
        $event->setData($data);

        return $event;
    }

    public function createFinishEvent($server, int $taskId, string $result): FinishEvent
    {
        $event = new FinishEvent();
        $event->setTaskId($taskId);
        $event->setResult($result);

        return $event;
    }

    public function createPipeMessageEvent($server, int $fromWorkerId, string $message): PipeMessageEvent
    {
        $event = new PipeMessageEvent();
        $event->setFromWorkerId($fromWorkerId);
        $event->setMessage($message);

        return $event;
    }

    public function createOpenEvent($server, $request): OpenEvent
    {
        $event = new OpenEvent();
        $event->setRequest($request);

        return $event;
    }

    public function createHandShakeEvent($request, $response): HandShakeEvent
    {
        $event = new HandShakeEvent();
        $event->setRequest($request);
        $event->setResponse($response);

        return $event;
    }

    public function createMessageEvent($server, Frame $frame): MessageEvent
    {
        $event = new MessageEvent();
        $event->setFrame($frame);

        return $event;
    }
}
