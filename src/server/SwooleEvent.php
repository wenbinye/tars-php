<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use wenbinye\tars\support\Enum;

class SwooleEvent extends Enum
{
    /**
     * Swoole onStart event.
     */
    const START = 'start';

    /**
     * Swoole onWorkerStart event.
     */
    const WORKER_START = 'workerStart';

    /**
     * Swoole onWorkerStop event.
     */
    const WORKER_STOP = 'workerStop';

    /**
     * Swoole onWorkerExit event.
     */
    const WORKER_EXIT = 'workerExit';

    /**
     * Swoole onWorkerErro event.
     */
    const WORKER_ERROR = 'workerError';

    /**
     * Swoole onPipeMessage event.
     */
    const PIPE_MESSAGE = 'pipeMessage';

    /**
     * Swoole onRequest event.
     */
    const REQUEST = 'request';

    /**
     * Swoole onReceive event.
     */
    const RECEIVE = 'receive';

    /**
     * Swoole onConnect event.
     */
    const CONNECT = 'connect';

    /**
     * Swoole onHandShake event.
     */
    const HAND_SHAKE = 'handshake';

    /**
     * Swoole onOpen event.
     */
    const OPEN = 'open';

    /**
     * Swoole onMessage event.
     */
    const MESSAGE = 'message';

    /**
     * Swoole onClose event.
     */
    const CLOSE = 'close';

    /**
     * Swoole onTask event.
     */
    const TASK = 'task';

    /**
     * Swoole onFinish event.
     */
    const FINISH = 'finish';

    /**
     * Swoole onShutdown event.
     */
    const SHUTDOWN = 'shutdown';

    /**
     * Swoole onPacket event.
     */
    const PACKET = 'packet';

    /**
     * Swoole onManagerStart event.
     */
    const MANAGER_START = 'managerStart';

    /**
     * Swoole onManagerStop event.
     */
    const MANAGER_STOP = 'managerStop';

    public static function requestEvents(): array
    {
        return [self::REQUEST, self::MESSAGE, self::RECEIVE];
    }
}
