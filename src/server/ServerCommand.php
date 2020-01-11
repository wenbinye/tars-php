<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\exception\ServerStateException;
use kuiper\swoole\ServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class ServerCommand extends Command
{
    const COMMAND_NAME = 'server';

    /**
     * @var ContainerFactoryInterface
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
        /** @var ServerInterface $server */
        $server = $this->containerFactory->create()->get(ServerInterface::class);
        try {
            $server->$action();

            return 0;
        } catch (ServerStateException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return -1;
        }
    }

    public function setContainerFactory(ContainerFactoryInterface $containerFactory): void
    {
        $this->containerFactory = $containerFactory;
    }
}
