<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\swoole\exception\ServerStateException;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

class ServerCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const COMMAND_NAME = 'server';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addArgument('action', InputArgument::OPTIONAL, 'action to perform: start|stop', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        Assert::oneOf($action, ['start', 'stop'], 'Unknown action \'%s\', expected one of: %s');
        try {
            if ('start' === $action) {
                $this->container->get(ServerInterface::class)->start();
            } elseif ('stop' === $action) {
                $this->container->get(ServerManager::class)->stop();
            }

            return 0;
        } catch (ServerStateException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return -1;
        }
    }
}
