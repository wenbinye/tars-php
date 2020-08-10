<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use kuiper\swoole\coroutine\Coroutine;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\server\SwooleServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const COMMAND_NAME = 'start';

    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * ServerStartCommand constructor.
     */
    public function __construct(ServerInterface $serverManager)
    {
        parent::__construct(self::COMMAND_NAME);
        $this->server = $serverManager;
    }

    protected function configure(): void
    {
        $this->setDescription('start php server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->server instanceof SwooleServer) {
            Coroutine::enable();
        }
        $this->server->start();

        return 0;
    }
}
