<?php

declare(strict_types=1);

namespace wenbinye\tars\server\http;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\server\SwooleServerSetting;
use wenbinye\tars\server\task\QueueInterface;

class ResponseSender implements ResponseSenderInterface
{
    /**
     * swoole default buffer_output_size.
     */
    const DEFAULT_BUFFER_OUTPUT_SIZE = 2097152;
    /**
     * Delay seconds to delete template response body file.
     */
    const DELETE_TEMP_FILE_DELAY = 5;

    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * @var QueueInterface
     */
    private $taskQueue;

    /**
     * ResponseSender constructor.
     */
    public function __construct(ServerProperties $serverProperties, QueueInterface $queue)
    {
        $this->serverProperties = $serverProperties;
        $this->taskQueue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function send(ResponseInterface $response, Response $swooleResponse): void
    {
        $swooleResponse->status($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }
        $body = $response->getBody();
        $contentLength = $body->getSize();
        $swooleResponse->header('content-length', (string) $contentLength);

        if ($body instanceof FileStream) {
            $swooleResponse->sendfile($body->getFileName());

            return;
        }

        if ($contentLength > $this->getBufferOutputSize()) {
            $file = tempnam(sys_get_temp_dir(), 'swoole-resp');
            file_put_contents($file, (string) $body);
            $swooleResponse->sendfile($file);
            $this->taskQueue->put(new DeleteFileTask($file, self::DELETE_TEMP_FILE_DELAY * 1000));
        } else {
            if ($contentLength > 0) {
                // $response->end($body) 在 1.9.8 版出现错误
                $swooleResponse->write((string) $body);
            }
            $swooleResponse->end();
        }
    }

    private function getBufferOutputSize(): int
    {
        $bufferOutputSize = $this->serverProperties->getSwooleServerSetting(SwooleServerSetting::BUFFER_OUTPUT_SIZE);
        if (!empty($bufferOutputSize)) {
            return (int) $bufferOutputSize;
        }

        return self::DEFAULT_BUFFER_OUTPUT_SIZE;
    }
}
