<?php

declare(strict_types=1);

namespace wenbinye\tars\server\task;

use kuiper\helper\Text;
use kuiper\swoole\task\ProcessorInterface;
use kuiper\swoole\task\Task;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use wenbinye\tars\server\exception\IOException;

class LogRotateProcessor implements ProcessorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const VERSION = 1;
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const ROTATE_STATUS_FILE = '.logrotate';

    protected const TAG = '['.__CLASS__.'] ';

    /**
     * @var array
     */
    private $logPaths;

    /**
     * @var string
     */
    private $suffixDateFormat;

    /**
     * LogRotateProcessor constructor.
     */
    public function __construct(array $logPaths, string $suffixDateFormat)
    {
        $this->logPaths = $logPaths;
        $this->suffixDateFormat = $suffixDateFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Task $task)
    {
        if (Text::isEmpty($this->suffixDateFormat)) {
            return;
        }
        $server = $task->getServer();
        $callback = function () use ($server) {
            try {
                if ($this->tryRotateLog()) {
                    $this->logger->info(static::TAG.'reload server since log rotated');
                    $server->reload();
                }
            } catch (\Throwable $e) {
                $this->logger->error(static::TAG.'fail to rotate log: '.$e);
            }
        };
        $server->tick(60000, $callback);
        $callback();
    }

    private function tryRotateLog(): bool
    {
        $suffixDateFormat = $this->suffixDateFormat;
        $rotateFiles = [];
        foreach ($this->logPaths as $logPath) {
            $oldStatus = $status = $this->readStatus($logPath);
            $now = time();
            foreach (glob($logPath.'/*.log') as $logFile) {
                if (!isset($status[$logFile])) {
                    $status[$logFile] = date(self::DATE_FORMAT, $now);
                }
                $suffix = date($suffixDateFormat, $now);
                if (date($suffixDateFormat, strtotime($status[$logFile])) !== $suffix) {
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
                $this->saveStatus($logPath, $status);
            }
        }

        return !empty($rotateFiles);
    }

    private function readStatus(string $logPath): array
    {
        $file = $this->getStatusFile($logPath);
        if (file_exists($file)) {
            $status = json_decode(file_get_contents($file), true);
            if (isset($status['version']) && self::VERSION === $status['version']) {
                return $status['files'];
            }
        }

        return [];
    }

    private function saveStatus(string $logPath, array $status): void
    {
        $statusFile = $this->getStatusFile($logPath);
        $ret = file_put_contents($statusFile, json_encode([
            'version' => self::VERSION,
            'files' => $status,
        ], JSON_PRETTY_PRINT));
        if (!$ret) {
            throw new IOException('Cannot write to '.$statusFile);
        }
    }

    private function getStatusFile(string $logPath): string
    {
        return $logPath.'/'.self::ROTATE_STATUS_FILE;
    }
}
