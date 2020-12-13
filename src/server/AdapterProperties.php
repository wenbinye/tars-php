<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\constants\ServerType;
use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\route\ServerAddress;
use wenbinye\tars\server\annotation\ConfigItem;

class AdapterProperties
{
    /**
     * @var array
     */
    private static $PROTOCOL_ALIAS = [
        'not_tars' => Protocol::HTTP,
    ];

    /**
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $name;

    /**
     * @ConfigItem(factory={ServerAddress::class, "fromString"})
     * @Assert\NotNull()
     *
     * @var ServerAddress|null
     */
    private $endpoint;
    /**
     * @ConfigItem(name="maxconns")
     *
     * @var int
     */
    private $maxConnections = 10000;
    /**
     * @ConfigItem()
     * @Assert\Choice(callback="protocols")
     * @Assert\NotBlank()
     *
     * @var string|null
     *
     * @see Protocol
     */
    private $protocol;
    /**
     * @ConfigItem(name="queuecap")
     *
     * @var int
     */
    private $queueCapacity = 50000;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $queueTimeout = 20000;
    /**
     * @ConfigItem(name="servant")
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $servantName;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $threads = 1;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ServerAddress|null
     */
    public function getEndpoint(): ?ServerAddress
    {
        return $this->endpoint;
    }

    /**
     * @param ServerAddress|null $endpoint
     */
    public function setEndpoint(?ServerAddress $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return int
     */
    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    /**
     * @param int $maxConnections
     */
    public function setMaxConnections(int $maxConnections): void
    {
        $this->maxConnections = $maxConnections;
    }

    /**
     * @return int
     */
    public function getQueueCapacity(): int
    {
        return $this->queueCapacity;
    }

    /**
     * @param int $queueCapacity
     */
    public function setQueueCapacity(int $queueCapacity): void
    {
        $this->queueCapacity = $queueCapacity;
    }

    /**
     * @return int
     */
    public function getQueueTimeout(): int
    {
        return $this->queueTimeout;
    }

    /**
     * @param int $queueTimeout
     */
    public function setQueueTimeout(int $queueTimeout): void
    {
        $this->queueTimeout = $queueTimeout;
    }

    /**
     * @return string|null
     */
    public function getServantName(): ?string
    {
        return $this->servantName;
    }

    /**
     * @param string|null $servantName
     */
    public function setServantName(?string $servantName): void
    {
        $this->servantName = $servantName;
    }

    /**
     * @return int
     */
    public function getThreads(): int
    {
        return $this->threads;
    }

    /**
     * @param int $threads
     */
    public function setThreads(int $threads): void
    {
        $this->threads = $threads;
    }

    /**
     * @return string|null
     */
    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol): void
    {
        if (isset(self::$PROTOCOL_ALIAS[$protocol])) {
            $protocol = self::$PROTOCOL_ALIAS[$protocol];
        }
        $this->protocol = $protocol;
    }

    public function getAdapterName(): string
    {
        return $this->servantName.'Adapter';
    }

    public function getServerType(): string
    {
        $protocol = Protocol::fromValue($this->protocol);
        if (null !== $protocol->serverType) {
            return $protocol->serverType;
        }
        if (ServerType::hasValue($this->endpoint->getProtocol())) {
            return $this->endpoint->getProtocol();
        }
        throw new \InvalidArgumentException('Cannot determine server type from protocol '.$this->protocol);
    }

    public function getSwooleSockType(): int
    {
        return ServerType::UDP === $this->getServerType() ? SWOOLE_SOCK_UDP : SWOOLE_SOCK_TCP;
    }

    public function protocols(): array
    {
        return Protocol::values();
    }
}
