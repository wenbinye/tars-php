<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\exception\ServerStateException;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class ServerCommand extends Command
{
    public const COMMAND_NAME = 'server';

    /**
     * @var ContainerFactoryInterface|callable
     */
    private $containerFactory;

    /**
     * @var ConfigLoaderInterface
     */
    private $configLoader;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addOption('define', 'D', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'config definition')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'config file')
            ->addArgument('action', InputArgument::OPTIONAL, 'action to perform: start|stop', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        Assert::oneOf($action, ['start', 'stop'], 'Unknown action \'%s\', expected one of: %s');
        $this->getConfigLoader()->load($input);
        try {
            if ('start' === $action) {
                $this->createContainer()->get(ServerInterface::class)->start();
            } elseif ('stop' === $action) {
                $this->createContainer()->get(ServerManager::class)->stop();
            }

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

    /**
     * @return ServerCommand
     */
    public function setConfigLoader(ConfigLoaderInterface $configLoader): void
    {
        $this->configLoader = $configLoader;
    }

    public function getConfigLoader(): ConfigLoaderInterface
    {
        if (!$this->configLoader) {
            $this->configLoader = new ConfigLoader();
        }

        return $this->configLoader;
    }

    private function createContainer(): ContainerInterface
    {
        if ($this->containerFactory instanceof ContainerFactoryInterface) {
            return $this->containerFactory->create();
        }

        return call_user_func($this->containerFactory);
    }
}
