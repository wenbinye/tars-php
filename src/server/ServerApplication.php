<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\annotation\Command;
use kuiper\di\ComponentCollection;
use kuiper\di\ContainerBuilder;
use kuiper\helper\Text;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use wenbinye\tars\deploy\PackageCommand;
use wenbinye\tars\server\event\BootstrapEvent;

class ServerApplication
{
    public const APP_NAME = 'tars-app';

    /**
     * @var ContainerFactoryInterface|callable|null
     */
    private $containerFactory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    /**
     * @var string|null
     */
    private $configFile;

    /**
     * @var self
     */
    private static $INSTANCE;

    public static function getInstance(): ServerApplication
    {
        if (null === self::$INSTANCE) {
            throw new \InvalidArgumentException('Call create first');
        }

        return self::$INSTANCE;
    }

    /**
     * @param ContainerFactoryInterface|callable|null $containerFactory
     *
     * @return ServerApplication
     */
    public static function create($containerFactory = null): ServerApplication
    {
        $serverApplication = new self();
        self::$INSTANCE = $serverApplication;
        $serverApplication->containerFactory = $containerFactory;

        return $serverApplication;
    }

    /**
     * @param ContainerFactoryInterface|callable|null $containerFactory
     *
     * @return int
     *
     * @throws \Exception
     */
    public static function run($containerFactory = null): int
    {
        $self = static::create($containerFactory);

        return $self->createApp(...$self->parseArgv())->run();
    }

    public static function package(): int
    {
        $app = new Application(self::APP_NAME);
        $command = new PackageCommand();
        $app->add($command);

        return $app->run();
    }

    public function getContainer(): ContainerInterface
    {
        if (null === $this->container) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    /**
     * @return string|null
     */
    public function getConfigFile(): ?string
    {
        return $this->configFile;
    }

    public function createApp(?string $configFile = null, array $properties = []): Application
    {
        if (null !== $configFile) {
            $this->configFile = $configFile;
            $this->getConfigLoader()->load($configFile, $properties);
        } else {
            Config::createDummyConfig();
        }
        $app = new Application(Config::getInstance()->getString('application.name', self::APP_NAME));

        $container = $this->getContainer();
        $commandLoader = new FactoryCommandLoader($this->getCommandMap($container));
        $app->setCommandLoader($commandLoader);
        $app->setDefaultCommand(ServerStartCommand::COMMAND_NAME);

        $container->get(EventDispatcherInterface::class)->dispatch(new BootstrapEvent($app));

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
        if (null === $this->containerFactory) {
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
        if (null === $this->configLoader) {
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
        $commands = $container->get('application.commands');
        if (null !== $commands) {
            if (!is_array($commands)) {
                throw new \InvalidArgumentException('application.commands should be an array');
            }
            foreach ($commands as $name => $id) {
                $commandMap[$name] = $factory($id);
            }
        }
        foreach (ComponentCollection::getAnnotations(Command::class) as $annotation) {
            /* @var Command $annotation */
            $commandMap[$annotation->name] = static function () use ($container, $annotation): ConsoleCommand {
                /** @var ConsoleCommand $command */
                $command = $container->get($annotation->getComponentId());
                if (Text::isEmpty($command->getName())) {
                    $command->setName($annotation->name);
                }

                return $command;
            };
        }

        return $commandMap;
    }
}
