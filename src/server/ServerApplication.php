<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use DI\ContainerBuilder;
use Symfony\Component\Console\Application;
use wenbinye\tars\deploy\PackageCommand;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    public static function run($containerFactory = null): int
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

    private static function createContainerFactory(): callable
    {
        return static function () {
            return ContainerBuilder::create(self::detectBasePath())->build();
        };
    }

    private static function detectBasePath(): string
    {
        if (defined('APP_PATH')) {
            $basePath = APP_PATH;
        } else {
            $libraryComposerJson = Composer::detect(__DIR__);
            $basePath = dirname($libraryComposerJson, 4);
            define('APP_PATH', $basePath);
        }

        if (!file_exists($basePath.'/vendor/autoload.php')
            || !file_exists($basePath.'/composer.json')) {
            throw new \InvalidArgumentException("Cannot detect project path, expected composer.json in $basePath");
        }

        return $basePath;
    }
}
