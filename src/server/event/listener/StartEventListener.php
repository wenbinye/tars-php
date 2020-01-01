<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\server\event\StartEvent;
use wenbinye\tars\server\exception\IOException;

class StartEventListener implements EventListenerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param StartEvent $event
     */
    public function __invoke($event): void
    {
        $serverProperties = $event->getServer()->getServerProperties();
        @cli_set_process_title($serverProperties->getServerName().': master process');

        try {
            $this->writePidFile($serverProperties->getMasterPidFile(), $event->getSwooleServer()->master_pid);
            $this->writePidFile($serverProperties->getManagerPidFile(), $event->getSwooleServer()->manager_pid);
        } catch (IOException $e) {
            $this->logger->error('Cannot write master and manager pid file: '.$e->getMessage());
            $event->getSwooleServer()->stop();
        }
    }

    private function writePidFile(string $pidFile, int $pid): void
    {
        $dir = dirname($pidFile);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            $this->logger->error("Cannot create pid file directory $dir");
            throw new IOException("Cannot create pid file directory $dir");
        }
        $ret = file_put_contents($pidFile, $pid);
        if (false === $ret) {
            throw new IOException("Cannot create pid file $pidFile");
        }
    }

    public function getSubscribedEvent(): string
    {
        return StartEvent::class;
    }
}