<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\exception\ServerStateException;
use kuiper\swoole\listener\ManagerStartEventListener;
use kuiper\swoole\listener\StartEventListener;
use kuiper\swoole\listener\TaskEventListener;
use kuiper\swoole\listener\WorkerStartEventListener;
use kuiper\swoole\server\ServerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use wenbinye\tars\rpc\middleware\RequestLogMiddleware;
use wenbinye\tars\server\listener\WorkerKeepAlive;
use wenbinye\tars\stat\collector\SystemCpuCollector;
use wenbinye\tars\stat\StatMiddleware;

class ServerCommand extends Command
{
    public const COMMAND_NAME = 'server';

    /**
     * @var ContainerFactoryInterface|callable
     */
    private $containerFactory;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'config file')
            ->addArgument('action', InputArgument::OPTIONAL, 'action to perform: start|stop', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        Assert::oneOf($action, ['start', 'stop'], 'Unknown action \'%s\', expected one of: %s');
        $configFile = $input->getOption('config');
        if (!$configFile) {
            throw new \InvalidArgumentException('config file is required');
        }
        if (!is_readable($configFile)) {
            throw new \InvalidArgumentException("config file '$configFile' is not readable");
        }
        Config::parseFile($configFile);
        $this->addDefaultConfig();
        /** @var ServerInterface $server */
        $server = $this->createContainer()->get(ServerInterface::class);
        try {
            $server->$action();

            return 0;
        } catch (ServerStateException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return -1;
        }
    }

    /**
     * @param ContainerFactoryInterface|callable $containerFactory
     */
    public function setContainerFactory($containerFactory): void
    {
        if (!$containerFactory instanceof ContainerFactoryInterface
            && !is_callable($containerFactory)) {
            throw new \InvalidArgumentException('Invalid container factory, expected instance of '.ContainerFactoryInterface::class.', got '.gettype($containerFactory));
        }
        $this->containerFactory = $containerFactory;
    }

    private function createContainer(): ContainerInterface
    {
        if ($this->containerFactory instanceof ContainerFactoryInterface) {
            return $this->containerFactory->create();
        } else {
            return call_user_func($this->containerFactory);
        }
    }

    private function addDefaultConfig(): void
    {
        Config::getInstance()->merge([
            'application' => [
                'monitor' => [
                    'collectors' => [
                        SystemCpuCollector::class,
                    ],
                ],
                'middleware' => [
                    'client' => [
                        StatMiddleware::class,
                        RequestLogMiddleware::class,
                    ],
                    'servant' => [
                        RequestLogMiddleware::class,
                    ],
                ],
                'listeners' => [
                    StartEventListener::class,
                    ManagerStartEventListener::class,
                    WorkerStartEventListener::class,
                    TaskEventListener::class,
                    WorkerKeepAlive::class,
                ],
            ],
        ]);
    }
}
