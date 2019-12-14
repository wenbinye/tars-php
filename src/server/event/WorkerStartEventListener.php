<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class WorkerStartEventListener implements EventListenerInterface
{
    /**
     * @param WorkerStartEvent $event
     */
    public function onEvent($event): void
    {
        $this->setProcessTitle($event);
//        if ($event->getWorkerId() === 0) {
//            // 将定时上报的任务投递到task worker 0,只需要投递一次
//            $this->sw->task(
//                [
//                    'application' => $this->application,
//                    'serverName' => $this->serverName,
//                    'masterPid' => $server->master_pid,
//                    'adapters' => array_column($this->tarsServerConfig['adapters'], 'adapterName'),
//                    'client' => $this->tarsClientConfig
//                ], 0);
//        }
    }

    private function setProcessTitle(WorkerStartEvent $event): void
    {
        $serverName = $event->getServer()->getServerProperties()->getServerName();
        if ($event->getSwooleServer()->taskworker) {
            @cli_set_process_title($serverName.": task worker {$event->getWorkerId()} process");
        } else {
            @cli_set_process_title($serverName.": worker {$event->getWorkerId()} process");
        }
    }
}
