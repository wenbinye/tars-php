<?php

declare(strict_types=1);

namespace wenbinye\tars\server\event;

class StartEventListener implements EventListenerInterface
{
    /**
     * @param StartEvent $event
     */
    public function onEvent($event): void
    {
        $serverProperties = $event->getServer()->getServerProperties();
        @cli_set_process_title($serverProperties->getServerName().': master process');

        file_put_contents($serverProperties->getMasterPidFile(), $event->getSwooleServer()->master_pid);
        file_put_contents($serverProperties->getManagerPidFile(), $event->getSwooleServer()->manager_pid);

        // 初始化的一次上报
        // TarsPlatform::keepaliveInit($this->tarsConfig, $server->master_pid);

        //拉取配置
//        if (!empty($this->servicesInfo) &&
//            isset($this->servicesInfo['saveTarsConfigFileDir']) &&
//            isset($this->servicesInfo['saveTarsConfigFileName'])) {
//            TarsPlatform::loadTarsConfig($this->tarsConfig,
//                $this->servicesInfo['saveTarsConfigFileDir'],
//                $this->servicesInfo['saveTarsConfigFileName']);
//        }
    }
}
