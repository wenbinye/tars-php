<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\swoole\constants\ServerSetting;
use kuiper\swoole\constants\ServerType;
use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\route\ServerAddress;
use wenbinye\tars\server\annotation\ConfigItem;

class ServerProperties
{
    private const DEFAULT_SETTINGS = [
        ServerSetting::OPEN_LENGTH_CHECK => true,
        ServerSetting::PACKAGE_LENGTH_TYPE => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 0,
        ServerSetting::PACKAGE_MAX_LENGTH => 10485760,
    ];

    /**
     * The App namespace.
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $app;

    /**
     * The server name.
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $server;

    /**
     * The swoole server settings.
     *
     * @var array
     */
    private $serverSettings = [];

    /**
     * The basepath config value, equal to "$TARSPATH/tarsnode/data/$app.$server/bin".
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $basePath;
    /**
     * The datapath config value, equal to "$TARSPATH/tarsnode/data/$app.$server/data".
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $dataPath;
    /**
     * The logpath config value, equal to "$TARSPATH/app_log".
     *
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $logPath;
    /**
     * @ConfigItem()
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $logLevel = 'DEBUG';
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $logSize = 15728640;  // 15M

    /**
     * @ConfigItem(factory={Route::class, "fromString"})
     *
     * @var Route|null
     */
    private $node;
    /**
     * @ConfigItem(factory={ServerAddress::class, "fromString"})
     *
     * @var ServerAddress|null
     */
    private $local;

    /**
     * @ConfigItem
     *
     * @var string|null
     */
    private $localIp;

    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $logServantName = 'tars.tarslog.LogObj';
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $configServantName = 'tars.tarsconfig.ConfigObj';
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $notifyServantName = 'tars.tarsnotify.NotifyObj';
    /**
     * @ConfigItem()
     *
     * @var string|null
     */
    private $startMode;
    /**
     * @ConfigItem()
     *
     * @var string|null
     */
    private $supervisorConfPath;
    /**
     * @ConfigItem()
     *
     * @var string
     */
    private $supervisorConfExtension = '.conf';
    /**
     * @ConfigItem()
     *
     * @var string|null
     */
    private $supervisorctl;
    /**
     * @Assert\Count(min=1)
     *
     * @var AdapterProperties[]
     */
    private $adapters = [];

    /**
     * @var AdapterProperties[][]
     */
    private $portAdapters = [];

    /**
     * @return string|null
     */
    public function getApp(): ?string
    {
        return $this->app;
    }

    /**
     * @param string|null $app
     */
    public function setApp(?string $app): void
    {
        $this->app = $app;
    }

    /**
     * @return string|null
     */
    public function getServer(): ?string
    {
        return $this->server;
    }

    /**
     * @param string|null $server
     */
    public function setServer(?string $server): void
    {
        $this->server = $server;
    }

    /**
     * @return array
     */
    public function getServerSettings(): array
    {
        return array_merge(self::DEFAULT_SETTINGS, $this->serverSettings);
    }

    /**
     * @param array $serverSettings
     */
    public function setServerSettings(array $serverSettings): void
    {
        $this->serverSettings = $serverSettings;
    }

    /**
     * @return string|null
     */
    public function getLogLevel(): ?string
    {
        return $this->logLevel;
    }

    /**
     * @param string|null $logLevel
     */
    public function setLogLevel(?string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogSize(): int
    {
        return $this->logSize;
    }

    /**
     * @param int $logSize
     */
    public function setLogSize(int $logSize): void
    {
        $this->logSize = $logSize;
    }

    /**
     * @return Route|null
     */
    public function getNode(): ?Route
    {
        return $this->node;
    }

    /**
     * @param Route|null $node
     */
    public function setNode(?Route $node): void
    {
        $this->node = $node;
    }

    /**
     * @return ServerAddress|null
     */
    public function getLocal(): ?ServerAddress
    {
        return $this->local;
    }

    /**
     * @param ServerAddress|null $local
     */
    public function setLocal(?ServerAddress $local): void
    {
        $this->local = $local;
    }

    /**
     * @return string|null
     */
    public function getLocalIp(): ?string
    {
        return $this->localIp;
    }

    /**
     * @param string|null $localIp
     */
    public function setLocalIp(?string $localIp): void
    {
        $this->localIp = $localIp;
    }

    /**
     * @return string
     */
    public function getLogServantName(): string
    {
        return $this->logServantName;
    }

    /**
     * @param string $logServantName
     */
    public function setLogServantName(string $logServantName): void
    {
        $this->logServantName = $logServantName;
    }

    /**
     * @return string
     */
    public function getConfigServantName(): string
    {
        return $this->configServantName;
    }

    /**
     * @param string $configServantName
     */
    public function setConfigServantName(string $configServantName): void
    {
        $this->configServantName = $configServantName;
    }

    /**
     * @return string
     */
    public function getNotifyServantName(): string
    {
        return $this->notifyServantName;
    }

    /**
     * @param string $notifyServantName
     */
    public function setNotifyServantName(string $notifyServantName): void
    {
        $this->notifyServantName = $notifyServantName;
    }

    /**
     * @return string|null
     */
    public function getStartMode(): ?string
    {
        return $this->startMode;
    }

    /**
     * @param string|null $startMode
     */
    public function setStartMode(?string $startMode): void
    {
        $this->startMode = $startMode;
    }

    /**
     * @return string|null
     */
    public function getSupervisorConfPath(): ?string
    {
        return $this->supervisorConfPath;
    }

    /**
     * @param string|null $supervisorConfPath
     */
    public function setSupervisorConfPath(?string $supervisorConfPath): void
    {
        $this->supervisorConfPath = $supervisorConfPath;
    }

    /**
     * @return string|null
     */
    public function getSupervisorctl(): ?string
    {
        return $this->supervisorctl;
    }

    /**
     * @param string|null $supervisorctl
     */
    public function setSupervisorctl(?string $supervisorctl): void
    {
        $this->supervisorctl = $supervisorctl;
    }

    /**
     * @return string
     */
    public function getSupervisorConfExtension(): string
    {
        return $this->supervisorConfExtension;
    }

    /**
     * @param string $supervisorConfExtension
     */
    public function setSupervisorConfExtension(string $supervisorConfExtension): void
    {
        $this->supervisorConfExtension = $supervisorConfExtension;
    }

    /**
     * @return array
     */
    public function getPortAdapters(): array
    {
        return $this->portAdapters;
    }

    /**
     * @param array $portAdapters
     */
    public function setPortAdapters(array $portAdapters): void
    {
        $this->portAdapters = $portAdapters;
    }

    public function getServerName(): string
    {
        return $this->app.'.'.$this->server;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getServerSetting(string $name)
    {
        return $this->serverSettings[$name] ?? null;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        if (!is_dir($basePath)) {
            throw new \InvalidArgumentException("basepath '$basePath' does not exist");
        }
        $this->basePath = rtrim(realpath($basePath), '/');
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
        if (!is_dir($dataPath) && !mkdir($dataPath) && !is_dir($dataPath)) {
            throw new \InvalidArgumentException("datapath '$dataPath' does not exist");
        }
        $this->dataPath = rtrim(realpath($dataPath), '/');
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    public function setLogPath(string $logPath): void
    {
        if (!is_dir($logPath) && !mkdir($logPath) && !is_dir($logPath)) {
            throw new \InvalidArgumentException("logpath '$logPath' does not exist");
        }
        $this->logPath = rtrim(realpath($logPath), '/');
    }

    public function getAppLogPath(): string
    {
        return sprintf('%s/%s/%s', $this->logPath, $this->app, $this->server);
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
        usort($adapters, static function (AdapterProperties $a, AdapterProperties $b) {
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

    public function getServerPidFile(): string
    {
        return $this->dataPath.'/'.$this->getServerName().'.pid';
    }

    public function isExternalMode(): bool
    {
        return 'external' === $this->startMode;
    }
}
