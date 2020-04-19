<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Application;
use wenbinye\tars\deploy\PackageCommand;
use wenbinye\tars\server\framework\Composer;
use wenbinye\tars\server\framework\ContainerFactory;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    public static function run(ContainerFactoryInterface $containerFactory = null): int
    {
        $app = new Application(self::APP_NAME);
        $command = new ServerCommand();
        $command->setContainerFactory($containerFactory ?? self::createContainerFactory());
        $app->add($command);
        $app->setDefaultCommand(ServerCommand::COMMAND_NAME, true);

        return $app->run();
    }

    public static function package(): int
    {
        $app = new Application(self::APP_NAME);
        $command = new PackageCommand();
        $app->add($command);

        return $app->run();
    }

    private static function createContainerFactory()
    {
        $composerJson = Composer::detect();
        $loader = require dirname($composerJson).'/vendor/autoload.php';
        $json = Composer::getJson($composerJson);
        $namespaces = [];
        if (!empty($json['autoload']['psr-4'])) {
            $namespaces[] = array_keys($json['autoload']['psr-4'])[0];
        }

        return new ContainerFactory($loader, $namespaces);
    }
}
