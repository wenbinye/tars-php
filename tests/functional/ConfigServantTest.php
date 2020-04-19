<?php

declare(strict_types=1);

namespace wenbinye\tars\config;

use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\functional\FunctionalTestCase;
use wenbinye\tars\rpc\middleware\RequestLogMiddleware;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\TarsClientInterface;
use wenbinye\tars\server\ServerProperties;

class ConfigServantTest extends FunctionalTestCase
{
    public function testLoadConfig()
    {
        $container = $this->getContainer();
        /** @var TarsClient $tarsClient */
        $tarsClient = $container->get(TarsClientInterface::class);
        $tarsClient->addMiddleware($container->make(RequestLogMiddleware::class, ['template' => RequestLogMiddleware::DEBUG]));
        $config = $container->get(ConfigServant::class);
        $serverProp = $container->get(ServerProperties::class);
        $config->loadConfig($serverProp->getApp(), $serverProp->getServer(), 'mysql', $mysqlConfig);
        var_export($mysqlConfig);
    }
}
