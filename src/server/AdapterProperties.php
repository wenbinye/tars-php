<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\Route;
use wenbinye\tars\server\annotation\ConfigItem;

class AdapterProperties
{
    /**
     * @ConfigItem(factory="fromString")
     * @Assert\NotNull()
     *
     * @var Route
     */
    private $endpoint;
    /**
     * @ConfigItem(name="maxconns")
     *
     * @var int
     */
    private $maxConnections;
    /**
     * @ConfigItem()
     * @Assert\Choice(choices={"http", "http2", "tcp", "udp", "grpc", "websocket", "tars", "not_tars"})
     *
     * @var string
     */
    private $protocol;
    /**
     * @ConfigItem(name="queuecap")
     *
     * @var int
     */
    private $queueCapacity;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $queueTimeout;
    /**
     * @ConfigItem(name="servant")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $servantName;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $threads;

    public function getEndpoint(): Route
    {
        return $this->endpoint;
    }

    public function setEndpoint(Route $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    public function setMaxConnections(int $maxConnections): void
    {
        $this->maxConnections = $maxConnections;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    public function getQueueCapacity(): int
    {
        return $this->queueCapacity;
    }

    public function setQueueCapacity(int $queueCapacity): void
    {
        $this->queueCapacity = $queueCapacity;
    }

    public function getQueueTimeout(): int
    {
        return $this->queueTimeout;
    }

    public function setQueueTimeout(int $queueTimeout): void
    {
        $this->queueTimeout = $queueTimeout;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function setServantName(string $servantName): void
    {
        $this->servantName = $servantName;
    }

    public function getThreads(): int
    {
        return $this->threads;
    }

    public function setThreads(int $threads): void
    {
        $this->threads = $threads;
    }
}
