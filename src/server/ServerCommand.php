<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;
use wenbinye\tars\support\ContainerFactoryInterface;

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
        $action = $input->getOption('action');
        Assert::oneOf($action, ['start', 'stop'], 'Unknown action \'%s\', expected one of: %s');
        Config::parseFile($input->getOption('config'));
        $this->containerFactory->create()->get(ServerInterface::class)
            ->$action();
    }

    public function setContainerFactory(ContainerFactoryInterface $containerFactory): void
    {
        $this->containerFactory = $containerFactory;
    }
}
