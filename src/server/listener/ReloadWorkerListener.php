<?php

declare(strict_types=1);

namespace wenbinye\tars\server\listener;

use kuiper\event\EventListenerInterface;
use kuiper\swoole\event\StartEvent;
use Webmozart\Assert\Assert;
use wenbinye\tars\server\ServerProperties;

/**
 * 服务启动一段时间后自动重新启动服务，缓解内存泄漏问题.
 */
class ReloadWorkerListener implements EventListenerInterface
{
    /**
     * @var ServerProperties
     */
    private $serverProperties;

    /**
     * ReloadListener constructor.
     *
     * @param ServerProperties $serverProperties
     */
    public function __construct(ServerProperties $serverProperties)
    {
        $this->serverProperties = $serverProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($event): void
    {
        Assert::isInstanceOf($event, StartEvent::class);
        /* @var StartEvent $event */
        if ($this->serverProperties->getReloadInterval() > 0) {
            $server = $event->getServer();
            $server->after($this->serverProperties->getReloadInterval() * 1000, function () use ($server) {
                $server->reload();
            });
        }
    }

    public function getSubscribedEvent(): string
    {
        return StartEvent::class;
    }
}
