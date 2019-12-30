<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use Swoole\Table;

class SwooleTableStatStore implements StatStoreAdapter
{
    /**
     * @var Table
     */
    protected $swooleTable;

    public function __construct(int $size = 4096)
    {
        $table = new Table($size);

        $table->column('count', Table::TYPE_INT, 4);
        $table->column('timeoutCount', Table::TYPE_INT, 4);
        $table->column('execCount', Table::TYPE_INT, 4);
        $table->column('totalRspTime', Table::TYPE_INT, 4);
        $table->column('maxRspTime', Table::TYPE_INT, 4);
        $table->column('minRspTime', Table::TYPE_INT, 4);
        $table->create();
        $this->swooleTable = $table;
    }

    public function save(StatEntry $entry): void
    {
        $key = $entry->getUniqueId();
        $body = $entry->getBody();
        if ($body->count > 0) {
            $this->swooleTable->incr($key, 'count', $body->count);
        } elseif ($body->execCount > 0) {
            $this->swooleTable->incr($key, 'execCount', $body->execCount);
        } elseif ($body->timeoutCount > 0) {
            $this->swooleTable->incr($key, 'timeoutCount', $body->timeoutCount);
        }
        $this->swooleTable->incr($key, 'totalRspTime', $body->totalRspTime);

        $this->swooleTable->set($key, [
            'maxRspTime' => max($body->maxRspTime, $this->swooleTable->get($key, 'maxRspTime')),
            'minRspTime' => min($body->minRspTime, $this->swooleTable->get($key, 'minRspTime')),
        ]);
    }

    public function delete(StatEntry $entry): void
    {
        $this->swooleTable->del($entry->getUniqueId());
    }

    /**
     * {@inheritdoc}
     */
    public function getEntries(int $maxIndex): \Iterator
    {
        foreach ($this->swooleTable as $key => $row) {
            $entry = StatEntry::fromString($key);
            if ($entry->getIndex() < $maxIndex) {
                $body = $entry->getBody();
                foreach ($row as $name => $value) {
                    $body->{$name} = $value;
                }
                yield $entry;
            }
        }
    }
}
