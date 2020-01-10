<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

interface TarsClientGeneratorInterface
{
    /**
     * Generates servant client proxy class.
     *
     * @param string $clientClassName the servant interface class name
     *
     * @return string the proxy class name
     */
    public function generate(string $clientClassName): string;
}
