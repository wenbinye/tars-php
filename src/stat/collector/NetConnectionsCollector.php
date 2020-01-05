<?php

declare(strict_types=1);

namespace wenbinye\tars\stat\collector;

class NetConnectionsCollector extends AbstractAvgCollector
{
    public function getValues(): array
    {
        exec("ps -ef -ww | grep {$serverName} | grep -v grep | awk '{print $2}'", $serverPidInfo);
        $tmpId = [];
        foreach ($serverPidInfo as $pid) {
            $tmpId[] = $pid.'/';
        }
        $grepPidInfo = implode('|', $tmpId);
        $command = "command -v netstat > /dev/null && netstat -anlp | grep tcp | grep -E '{$grepPidInfo}' | awk '{print $6}' | awk -F: '{print $1}'|sort|uniq -c|sort -nr";
        exec($command, $netStatInfo);
        foreach ($netStatInfo as $statInfo) {
            $statArr = explode(' ', trim($statInfo));
            $msgHead = [
                'ip' => $ip,
                'propertyName' => $serverName.'.netStat.'.$statArr[1],
            ];
            $msgBody = [
                'policy' => 'Avg',
                'value' => isset($statArr[0]) ? (int) $statArr[0] : 0,
            ];
            $msgHeadArr[] = $msgHead;
            $msgBodyArr[] = $msgBody;
        }
    }
}
