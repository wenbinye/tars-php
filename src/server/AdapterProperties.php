<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\Route;
use wenbinye\tars\server\annotation\ConfigItem;

class AdapterProperties
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

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
     * @Assert\Choice(callback="protocols")
     * @Assert\NotBlank()
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
    /**
     * @var int
     */
    private $swooleSockType;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

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

    public function getSwooleServerType(): string
    {
        return Protocol::fromValue($this->protocol)->serverType
            ?: $this->endpoint->getProtocol();
    }

    public function getSwooleSockType(): int
    {
        return $this->swooleSockType
            ?? (SwooleServerType::UDP === $this->getSwooleServerType() ? SWOOLE_SOCK_UDP : SWOOLE_SOCK_TCP);
    }

    public function setSwooleSockType(int $swooleSockType): void
    {
        $this->swooleSockType = $swooleSockType;
    }

    public function protocols(): array
    {
        return Protocol::values();
    }
}
