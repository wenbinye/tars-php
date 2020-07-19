<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const COMMAND_NAME = 'stop';

    /**
     * @var ServerManager
     */
    private $serverManager;

    /**
     * ServerStartCommand constructor.
     *
     * @param ServerInterface $serverManager
     */
    public function __construct(ServerManager $serverManager)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->serverManager = $serverManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->serverManager->stop();

        return 0;
    }
}
