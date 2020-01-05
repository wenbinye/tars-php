<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use Monolog\Logger;
use wenbinye\tars\log\TarsLogHandler;

class LogTest extends FunctionalTestCase
{
    public function testLog()
    {
        $logger = new Logger('app');
        $container = $this->getContainer();
        $handler = $container->get(TarsLogHandler::class);
        $logger->pushHandler($handler);

        $logger->info('hello', ['message' => 'world']);
    }
}
