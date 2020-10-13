<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

interface StatStoreAdapter
{
    public function save(StatEntry $entry): void;

    public function delete(StatEntry $entry): void;

    /**
     * @param int $maxIndex
     *
     * @return \Iterator<StatEntry>
     */
    public function getEntries(int $maxIndex): \Iterator;
}
