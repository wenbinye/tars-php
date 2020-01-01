<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event\listener;

interface EventListenerInterface
{
    /**
     * @param object $event
     */
    public function __invoke($event): void;

    public function getSubscribedEvent(): string;
}
