<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Application;
use wenbinye\tars\deploy\PackageCommand;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    public static function run(ContainerFactoryInterface $containerFactory): int
    {
        $app = new Application(self::APP_NAME);
        $command = new ServerCommand();
        $command->setContainerFactory($containerFactory);
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
}
