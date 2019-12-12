<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\rpc\Route;
use wenbinye\tars\server\annotation\ConfigItem;

class ClientProperties
{
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $asyncThread;
    /**
     * @ConfigItem(factory="fromString")
     *
     * @var Route
     */
    private $locator;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $syncInvokeTimeout;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $asyncInvokeTimeout;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $refreshEndpointInterval;
    /**
     * @ConfigItem()
     *
     * @var int
     */
    private $reportInterval;
    /**
     * @ConfigItem
     *
     * @var string
     */
    private $statServantName;
    /**
     * @ConfigItem
     *
     * @var string
     */
    private $propertyServantName;
    /**
     * @ConfigItem
     *
     * @var string
     */
    private $moduleName;
    /**
     * @ConfigItem
     *
     * @var int
     */
    private $sampleRate;
    /**
     * @ConfigItem
     *
     * @var int
     */
    private $maxSampleCount;

    public function getAsyncThread(): int
    {
        return $this->asyncThread;
    }

    public function getLocator(): Route
    {
        return $this->locator;
    }

    public function getSyncInvokeTimeout(): int
    {
        return $this->syncInvokeTimeout;
    }

    public function getAsyncInvokeTimeout(): int
    {
        return $this->asyncInvokeTimeout;
    }

    public function getRefreshEndpointInterval(): int
    {
        return $this->refreshEndpointInterval;
    }

    public function getReportInterval(): int
    {
        return $this->reportInterval;
    }

    public function getStatServantName(): string
    {
        return $this->statServantName;
    }

    public function getPropertyServantName(): string
    {
        return $this->propertyServantName;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getSampleRate(): int
    {
        return $this->sampleRate;
    }

    public function getMaxSampleCount(): int
    {
        return $this->maxSampleCount;
    }

    public function setAsyncThread(int $asyncThread): void
    {
        $this->asyncThread = $asyncThread;
    }

    public function setLocator(Route $locator): void
    {
        $this->locator = $locator;
    }

    public function setSyncInvokeTimeout(int $syncInvokeTimeout): void
    {
        $this->syncInvokeTimeout = $syncInvokeTimeout;
    }

    public function setAsyncInvokeTimeout(int $asyncInvokeTimeout): void
    {
        $this->asyncInvokeTimeout = $asyncInvokeTimeout;
    }

    public function setRefreshEndpointInterval(int $refreshEndpointInterval): void
    {
        $this->refreshEndpointInterval = $refreshEndpointInterval;
    }

    public function setReportInterval(int $reportInterval): void
    {
        $this->reportInterval = $reportInterval;
    }

    public function setStatServantName(string $statServantName): void
    {
        $this->statServantName = $statServantName;
    }

    public function setPropertyServantName(string $propertyServantName): void
    {
        $this->propertyServantName = $propertyServantName;
    }

    public function setModuleName(string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }

    public function setSampleRate(int $sampleRate): void
    {
        $this->sampleRate = $sampleRate;
    }

    public function setMaxSampleCount(int $maxSampleCount): void
    {
        $this->maxSampleCount = $maxSampleCount;
    }

    public static function fromConfig(Config $config, Reader $annotationReader): ClientProperties
    {
        $clientProperties = new static();
        $config->tars->application->client->updateTo($clientProperties, $annotationReader);

        return $clientProperties;
    }
}
