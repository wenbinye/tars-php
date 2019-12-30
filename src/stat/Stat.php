<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use wenbinye\tars\protocol\type\StructMap;
use wenbinye\tars\rpc\ResponseInterface;

class Stat implements StatInterface
{
    /**
     * @var StatStoreAdapter
     */
    private $store;

    /**
     * @var StatFClient
     */
    private $statClient;

    /**
     * @var int
     */
    private $reportInterval;

    public function success(ResponseInterface $response, int $responseTime): void
    {
        $this->store->save(StatEntry::success($this->getTimeSlice(), $response, $responseTime));
    }

    public function fail(ResponseInterface $response, int $responseTime): void
    {
        $this->store->save(StatEntry::fail($this->getTimeSlice(), $response, $responseTime));
    }

    public function timedOut(ResponseInterface $response, int $responseTime): void
    {
        $this->store->save(StatEntry::timedOut($this->getTimeSlice(), $response, $responseTime));
    }

    public function send(): void
    {
        $msg = new StructMap();
        $currentSlice = $this->getTimeSlice();
        foreach ($this->store->getEntries($currentSlice) as $entry) {
            $msg->put($entry->getHead(), $entry->getBody());
        }
        if ($msg->count() > 0) {
            $this->statClient->reportMicMsg($msg, true);
            foreach ($msg as $entry) {
                $this->store->delete($entry);
            }
        }
    }

    private function getTimeSlice(): int
    {
        $time = time();

        return (int) ($time / ($this->reportInterval / 1000));
    }
}
