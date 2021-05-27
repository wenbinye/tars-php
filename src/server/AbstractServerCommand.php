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

    protected function withFileLock(string $filePrefix, callable $callback): bool
    {
        $lockFile = $filePrefix.'.lock';
        if (file_exists($lockFile) && filemtime($lockFile) < time() - 360) {
            // 锁文件超时
            $this->logger->info(static::TAG."lock file $lockFile expired");
            unlink($lockFile);
        }
        $timeout = 60;
        $fp = fopen($lockFile, 'wb+');
        while ($timeout > 0) {
            if (false === $fp) {
                $this->logger->error(static::TAG."Cannot create lock file $lockFile");
            } elseif (flock($fp, LOCK_EX | LOCK_NB)) { // 进行排它型锁定
                $this->logger->info(static::TAG."obtain lock file $lockFile");
                try {
                    $callback();
                } catch (\Exception $e) {
                    $this->logger->error(static::TAG.'fail to execute '.$e->getMessage());
                }
                flock($fp, LOCK_UN);    // 释放锁定
                fclose($fp);
                $this->logger->info(static::TAG."release lock file $lockFile");
                if (file_exists($lockFile) && !unlink($lockFile)) {
                    throw new \RuntimeException("Cannot delete lock file $lockFile");
                }

                return true;
            }
            sleep(1);
            --$timeout;
        }
        $this->logger->error(static::TAG."Fail to obtain lock file $lockFile");

        return false;
    }
}
