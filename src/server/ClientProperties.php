<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Symfony\Component\Validator\Constraints as Assert;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\server\annotation\ConfigItem;

class ClientProperties
{
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $asyncThread = 3;
    /**
     * @ConfigItem(factory={Route::class, "fromString"})
     *
     * @var Route|null
     */
    private $locator;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $syncInvokeTimeout = 20000;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $asyncInvokeTimeout = 20000;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $refreshEndpointInterval = 60000;
    /**
     * @ConfigItem()
     * @Assert\Range(min=1000)
     *
     * @var int
     */
    private $keepAliveInterval = 20000;
    /**
     * @ConfigItem()
     * @Assert\Range(min=1000)
     *
     * @var int
     */
    private $reportInterval = 60000;
    /**
     * @ConfigItem
     *
     * @var string
     */
    private $statServantName = 'tars.tarsstat.StatObj';
    /**
     * @ConfigItem
     *
     * @var string
     */
    private $propertyServantName = 'tars.tarsproperty.PropertyObj';
    /**
     * @ConfigItem
     * @Assert\NotBlank()
     *
     * @var string|null
     */
    private $moduleName;
    /**
     * @ConfigItem
     *
     * @var int
     */
    private $sampleRate = 100000;
    /**
     * @ConfigItem
     *
     * @var int
     */
    private $maxSampleCount = 50;

    /**
     * @return int
     */
    public function getAsyncThread(): int
    {
        return $this->asyncThread;
    }

    /**
     * @param int $asyncThread
     */
    public function setAsyncThread(int $asyncThread): void
    {
        $this->asyncThread = $asyncThread;
    }

    /**
     * @return Route|null
     */
    public function getLocator(): ?Route
    {
        return $this->locator;
    }

    /**
     * @param Route|null $locator
     */
    public function setLocator(?Route $locator): void
    {
        $this->locator = $locator;
    }

    /**
     * @return int
     */
    public function getSyncInvokeTimeout(): int
    {
        return $this->syncInvokeTimeout;
    }

    /**
     * @param int $syncInvokeTimeout
     */
    public function setSyncInvokeTimeout(int $syncInvokeTimeout): void
    {
        $this->syncInvokeTimeout = $syncInvokeTimeout;
    }

    /**
     * @return int
     */
    public function getAsyncInvokeTimeout(): int
    {
        return $this->asyncInvokeTimeout;
    }

    /**
     * @param int $asyncInvokeTimeout
     */
    public function setAsyncInvokeTimeout(int $asyncInvokeTimeout): void
    {
        $this->asyncInvokeTimeout = $asyncInvokeTimeout;
    }

    /**
     * @return int
     */
    public function getRefreshEndpointInterval(): int
    {
        return $this->refreshEndpointInterval;
    }

    /**
     * @param int $refreshEndpointInterval
     */
    public function setRefreshEndpointInterval(int $refreshEndpointInterval): void
    {
        $this->refreshEndpointInterval = $refreshEndpointInterval;
    }

    /**
     * @return int
     */
    public function getKeepAliveInterval(): int
    {
        return $this->keepAliveInterval;
    }

    /**
     * @param int $keepAliveInterval
     */
    public function setKeepAliveInterval(int $keepAliveInterval): void
    {
        $this->keepAliveInterval = $keepAliveInterval;
    }

    /**
     * @return int
     */
    public function getReportInterval(): int
    {
        return $this->reportInterval;
    }

    /**
     * @param int $reportInterval
     */
    public function setReportInterval(int $reportInterval): void
    {
        $this->reportInterval = $reportInterval;
    }

    /**
     * @return string
     */
    public function getStatServantName(): string
    {
        return $this->statServantName;
    }

    /**
     * @param string $statServantName
     */
    public function setStatServantName(string $statServantName): void
    {
        $this->statServantName = $statServantName;
    }

    /**
     * @return string
     */
    public function getPropertyServantName(): string
    {
        return $this->propertyServantName;
    }

    /**
     * @param string $propertyServantName
     */
    public function setPropertyServantName(string $propertyServantName): void
    {
        $this->propertyServantName = $propertyServantName;
    }

    /**
     * @return string|null
     */
    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    /**
     * @param string|null $moduleName
     */
    public function setModuleName(?string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return int
     */
    public function getSampleRate(): int
    {
        return $this->sampleRate;
    }

    /**
     * @param int $sampleRate
     */
    public function setSampleRate(int $sampleRate): void
    {
        $this->sampleRate = $sampleRate;
    }

    /**
     * @return int
     */
    public function getMaxSampleCount(): int
    {
        return $this->maxSampleCount;
    }

    /**
     * @param int $maxSampleCount
     */
    public function setMaxSampleCount(int $maxSampleCount): void
    {
        $this->maxSampleCount = $maxSampleCount;
    }
}
