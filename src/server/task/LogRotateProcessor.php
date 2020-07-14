<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\task\ProcessorInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\server\exception\IOException;
use wenbinye\tars\server\ServerProperties;

class LogRotateProcessor implements ProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const VERSION = 1;
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const ROTATE_STATUS_FILE = '.logrotate';

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var ServerInterface
     */
    private $server;

    /**
     * @var string
     */
    private $logPath;

    /**
     * LogRotateProcessor constructor.
     */
    public function __construct(ServerInterface $server, ServerProperties $serverProperties)
    {
        $this->server = $server;
        $this->logPath = $serverProperties->getAppLogPath();
    }

    /**
     * {@inheritdoc}
     *
     * @param LogRotate $task
     */
    public function process($task)
    {
        $this->tryRotateLog($task);
        $this->server->tick(60000, function () use ($task) {
            $this->tryRotateLog($task);
        });
    }

    private function tryRotateLog(LogRotate $config): void
    {
        $oldStatus = $status = $this->readStatus();
        $rotateFiles = [];
        $now = time();
        foreach (glob($this->logPath.'/*.log') as $logFile) {
            if (!isset($status[$logFile])) {
                $status[$logFile] = date(self::DATE_FORMAT, $now);
            }
            $suffix = date($config->getSuffix(), $now);
            if (date($config->getSuffix(), strtotime($status[$logFile])) !== $suffix) {
                $rotateFile = $logFile.$suffix;
                if (!file_exists($rotateFile)) {
                    if (!rename($logFile, $rotateFile)) {
                        $this->logger->error(static::TAG.'fail to rename file', [
                            'from' => $logFile,
                            'to' => $rotateFile,
                        ]);
                    } else {
                        $status[$logFile] = date(self::DATE_FORMAT, $now);
                        $rotateFiles[] = $rotateFile;
                    }
                }
            }
        }
        if ($status != $oldStatus) {
            $this->saveStatus($status);
        }
        if (empty($rotateFiles)) {
            return;
        }
        $this->logger->info(static::TAG.'reload server since log rotated');
        $this->server->reload();
    }

    private function readStatus(): array
    {
        $file = $this->getStatusFile();
        if (file_exists($file)) {
            $status = json_decode(file_get_contents($file), true);
            if (isset($status['version']) && self::VERSION === $status['version']) {
                return $status['files'];
            }
        }

        return [];
    }

    private function saveStatus(array $status): void
    {
        $ret = file_put_contents($this->getStatusFile(), json_encode([
            'version' => self::VERSION,
            'files' => $status,
        ], JSON_PRETTY_PRINT));
        if (!$ret) {
            throw new IOException('Cannot write to '.$this->getStatusFile());
        }
    }

    private function getStatusFile(): string
    {
        return $this->logPath.'/'.self::ROTATE_STATUS_FILE;
    }
}
