<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\di\ContainerAwareInterface;
use kuiper\di\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;

abstract class AbstractServerCommand extends Command implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    protected const TAG = '['.__CLASS__.'] ';

    protected function withFileLock(string $filePrefix, callable $callback): void
    {
        $lockFile = $filePrefix.'.lock';
        if (file_exists($lockFile) && filemtime($lockFile) < time() - 360) {
            // 锁文件超时
            $this->logger->info(static::TAG."lock file $lockFile expired");
            unlink($lockFile);
        }
        $timeout = 60;
        while ($timeout > 0) {
            $fp = fopen($lockFile, 'wb+');
            if (false === $fp) {
                $this->logger->error(static::TAG."Cannot create lock file $lockFile");
            } elseif (!flock($fp, LOCK_EX | LOCK_NB)) { // 进行排它型锁定
                try {
                    $callback();
                } catch (\Exception $e) {
                    $this->logger->error(static::TAG.'fail to execute '.$e->getMessage());
                }
                flock($fp, LOCK_UN);    // 释放锁定
                fclose($fp);
                if (file_exists($lockFile) && !unlink($lockFile)) {
                    throw new \RuntimeException("Cannot delete lock file $lockFile");
                }

                return;
            }
            sleep(1);
            --$timeout;
        }
        $this->logger->error(static::TAG."Fail to obtain lock file $lockFile");
    }
}
