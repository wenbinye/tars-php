<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\constants\ServerType;
use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\ServerAddress;
use wenbinye\tars\server\annotation\ConfigItem;

class ServerProperties
{
    /**
     * The App namespace.
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $app;

    /**
     * The server name.
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $server;

    /**
     * The swoole server settings.
     *
     * @var array
     */
    private $serverSettings;

    /**
     * The basepath config value, equal to "$TARSPATH/tarsnode/data/$app.$server/bin".
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $basePath;
    /**
     * The datapath config value, equal to "$TARSPATH/tarsnode/data/$app.$server/data".
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $dataPath;
    /**
     * The logpath config value, equal to "$TARSPATH/app_log".
     *
     * @ConfigItem()
     *
     * @var string
     */
    private $logPath;
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $logLevel;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $logSize;

    /**
     * @ConfigItem(factory="wenbinye\tars\rpc\route\Route::fromString")
     *
     * @var ?Route
     */
    private $node;
    /**
     * @ConfigItem(factory="wenbinye\tars\rpc\route\ServerAddress::fromString")
     *
     * @var ServerAddress
     */
    private $local;

    /**
     * @ConfigItem
     *
     * @var string
     */
    private $localIp;

    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $logServantName;
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $configServantName;
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $notifyServantName;
    /**
     * @Assert\Count(min=1)
     *
     * @var AdapterProperties[]
     */
    private $adapters = [];

    /**
     * @var array
     */
    private $portAdapters;

    public function getApp(): string
    {
        return $this->app;
    }

    public function setApp(string $app): void
    {
        $this->app = $app;
    }

    public function getServer(): string
    {
        return $this->server;
    }

    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    public function getServerName(): string
    {
        return $this->app.'.'.$this->server;
    }

    public function getServerSettings(): array
    {
        return $this->serverSettings;
    }

    public function getServerSetting(string $name)
    {
        return $this->serverSettings[$name] ?? null;
    }

    public function setServerSettings(array $serverSettings): void
    {
        $this->serverSettings = $serverSettings;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function getSourcePath(): string
    {
        return $this->basePath.'/src';
    }

    public function getDataPath(): string
    {
        return $this->dataPath;
    }

    public function setDataPath(string $dataPath): void
    {
        $this->dataPath = rtrim($dataPath, '/');
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    public function setLogPath(string $logPath): void
    {
        $this->logPath = rtrim($logPath, '/');
    }

    public function getAppLogPath(): string
    {
        return sprintf('%s/%s/%s', $this->logPath, $this->app, $this->server);
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function getLogSize(): int
    {
        return $this->logSize;
    }

    public function setLogSize(int $logSize): void
    {
        $this->logSize = $logSize;
    }

    public function getNode(): ?Route
    {
        return $this->node;
    }

    public function setNode(Route $node): void
    {
        $this->node = $node;
    }

    public function getLocal(): ServerAddress
    {
        return $this->local;
    }

    public function setLocal(ServerAddress $local): void
    {
        $this->local = $local;
    }

    public function getLocalIp(): string
    {
        return $this->localIp;
    }

    public function setLocalIp(string $localIp): void
    {
        $this->localIp = $localIp;
    }

    public function getLogServantName(): string
    {
        return $this->logServantName;
    }

    public function setLogServantName(string $logServantName): void
    {
        $this->logServantName = $logServantName;
    }

    public function getConfigServantName(): string
    {
        return $this->configServantName;
    }

    public function setConfigServantName(string $configServantName): void
    {
        $this->configServantName = $configServantName;
    }

    public function getNotifyServantName(): string
    {
        return $this->notifyServantName;
    }

    public function setNotifyServantName(string $notifyServantName): void
    {
        $this->notifyServantName = $notifyServantName;
    }

    /**
     * @return AdapterProperties[]
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * @param AdapterProperties[] $adapters
     */
    public function setAdapters(array $adapters): void
    {
        usort($adapters, function (AdapterProperties $a, AdapterProperties $b) {
            if ($b->getServerType() === $a->getServerType()) {
                return 0;
            }
            if (ServerType::fromValue($a->getServerType())->isHttpProtocol()) {
                return -1;
            }

            return 1;
        });
        $this->portAdapters = [];
        foreach ($adapters as $adapter) {
            $this->portAdapters[$adapter->getEndpoint()->getPort()][] = $adapter;
        }

        $this->adapters = $adapters;
    }

    public function hasAdapter(string $name): bool
    {
        return isset($this->adapters[$name]);
    }

    public function getAdapter(string $name): ?AdapterProperties
    {
        return $this->adapters[$name] ?? null;
    }

    public function getPrimaryAdapter(): AdapterProperties
    {
        return array_values($this->adapters)[0];
    }

    /**
     * @return AdapterProperties[]
     */
    public function getAdaptersByPort(int $port): array
    {
        return $this->portAdapters[$port] ?? [];
    }

    public function getMasterPidFile(): string
    {
        return $this->dataPath.'/master.pid';
    }

    public function getManagerPidFile(): string
    {
        return $this->dataPath.'/manager.pid';
    }
}
