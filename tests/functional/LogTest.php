<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use Monolog\Logger;
use wenbinye\tars\log\TarsLogHandler;
use wenbinye\tars\rpc\connection\ConnectionFactoryInterface;
use wenbinye\tars\rpc\middleware\RequestLog;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\TarsClientInterface;

class LogTest extends FunctionalTestCase
{
    public function testLog()
    {
        $logger = new Logger('app');
        $container = $this->getContainer();
        $containerFactory = $container->get(ConnectionFactoryInterface::class);
        /** @var TarsClient $tarsClient */
        $tarsClient = $container->get(TarsClientInterface::class);
        $tarsClient->addMiddleware($container->make(RequestLog::class, ['template' => RequestLog::MAIN]));
        $handler = $container->get(TarsLogHandler::class);
        $logger->pushHandler($handler);

        $logger->info('hello', ['message' => 'world']);
        $this->assertTrue(true);
    }
}
