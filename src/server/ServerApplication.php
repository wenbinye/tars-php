<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\annotation\Command;
use kuiper\di\ComponentCollection;
use kuiper\di\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use wenbinye\tars\deploy\PackageCommand;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    /**
     * @var ContainerFactoryInterface|callable
     */
    private $containerFactory;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    public static function create($containerFactory = null): ServerApplication
    {
        $serverApplication = new self();
        $serverApplication->containerFactory = $containerFactory;

        return $serverApplication;
    }

    public static function run($containerFactory = null): int
    {
        return static::create($containerFactory)->createApp()->run();
    }

    public static function package(): int
    {
        $app = new Application(self::APP_NAME);
        $command = new PackageCommand();
        $app->add($command);

        return $app->run();
    }

    public function createApp(): Application
    {
        $configOptions = $this->parseArgv();
        if ($configOptions[0]) {
            $this->getConfigLoader()->load(...$configOptions);
        }
        if (!isset($configOptions[0])) {
            Config::createDummyConfig();
        }
        $container = $this->createContainer();
        $commandLoader = new FactoryCommandLoader($this->getCommandMap($container));

        $app = new Application(self::APP_NAME);
        $app->setCommandLoader($commandLoader);
        $app->setDefaultCommand(ServerStartCommand::COMMAND_NAME);

        return $app;
    }

    public static function detectBasePath(): string
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

    public function setConfigLoader(ConfigLoaderInterface $configLoader): void
    {
        $this->configLoader = $configLoader;
    }

    protected function createContainer(): ContainerInterface
    {
        if (!$this->containerFactory) {
            return ContainerBuilder::create(defined('APP_PATH') ? APP_PATH : self::detectBasePath())
                ->build();
        }
        if ($this->containerFactory instanceof ContainerFactoryInterface) {
            return $this->containerFactory->create();
        }

        return call_user_func($this->containerFactory);
    }

    protected function getConfigLoader(): ConfigLoaderInterface
    {
        if (!$this->configLoader) {
            $this->configLoader = new ConfigLoader();
        }

        return $this->configLoader;
    }

    private function parseArgv(): array
    {
        $configFile = null;
        $properties = [];
        $commandName = null;
        $argv = $_SERVER['argv'];
        $rest = [];
        while (null !== $token = array_shift($argv)) {
            if ('--' === $token) {
                $rest[] = $token;
                break;
            }
            if (0 === strpos($token, '--')) {
                $name = substr($token, 2);
                $pos = strpos($name, '=');
                if (false !== $pos) {
                    $value = substr($name, $pos + 1);
                    $name = substr($name, 0, $pos);
                }
                if ('config' === $name) {
                    $configFile = $value ?? array_shift($argv);
                } elseif ('define' === $name) {
                    $properties[] = $value ?? array_shift($argv);
                } else {
                    $rest[] = $token;
                }
            } elseif ('-' === $token[0] && 2 === strlen($token) && 'D' === $token[1]) {
                $properties[] = array_shift($argv);
            } else {
                $rest[] = $token;
            }
        }
        $_SERVER['argv'] = array_merge($rest, $argv);

        return [$configFile, $properties];
    }

    protected function getCommandMap(ContainerInterface $container): array
    {
        $factory = static function ($id) use ($container) {
            return static function () use ($container, $id) {
                return $container->get($id);
            };
        };
        $commandMap = [
            ServerStartCommand::COMMAND_NAME => $factory(ServerStartCommand::class),
            ServerStopCommand::COMMAND_NAME => $factory(ServerStopCommand::class),
        ];
        if ($container->has('application.commands')
            && $container->get('application.commands')) {
            $commands = $container->get('application.commands');
            if (!is_array($commands)) {
                throw new \InvalidArgumentException('application.commands should be an array');
            }
            foreach ($commands as $name => $id) {
                $commandMap[$name] = $factory($id);
            }
        }
        foreach (ComponentCollection::getAnnotations(Command::class) as $annotation) {
            /* @var Command $annotation */
            $commandMap[$annotation->name] = function () use ($container, $annotation) {
                /** @var \Symfony\Component\Console\Command\Command $command */
                $command = $container->get($annotation->getComponentId());
                if (!$command->getName()) {
                    $command->setName($annotation->name);
                }

                return $command;
            };
        }

        return $commandMap;
    }
}
