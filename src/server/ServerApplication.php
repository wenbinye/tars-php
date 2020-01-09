<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Application;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    /**
     * ServerApplication constructor.
     *
     * @throws \Exception
     */
    public static function run(ContainerFactoryInterface $containerFactory): int
    {
        $app = new Application(self::APP_NAME);
        $command = new ServerCommand();
        $command->setContainerFactory($containerFactory);
        $app->add($command);
        $app->setDefaultCommand(ServerCommand::COMMAND_NAME, true);

        return $app->run();
    }
}
